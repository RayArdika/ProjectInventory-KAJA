<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Distribution;
use App\Models\Product;
use App\Models\Production;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DistributionController extends Controller
{
    public function index(Request $request)
    {
        $distributionQuery = $this->filteredQuery($request);

        $distributions = (clone $distributionQuery)
            ->with(['items.product'])
            ->latest('distribution_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $approvedProductNames = Production::where('status', 'Approved')
            ->pluck('product_name')
            ->unique();

        $products = Product::whereIn('product_name', $approvedProductNames)
            ->where('stock', '>', 0)
            ->orderBy('sku')
            ->get();

        $totalSalesAmount = (clone $distributionQuery)->sum('total_amount');
        $totalUnpaidAmount = (clone $distributionQuery)->selectRaw(
            'SUM(CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END) as total'
        )->value('total') ?? 0;

        $topClients = Distribution::select('destination')
            ->selectRaw('COUNT(*) as total_orders')
            ->selectRaw('SUM(qty_out) as total_qty')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->groupBy('destination')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $clients = Distribution::query()
            ->whereNotNull('destination')
            ->where('destination', '!=', '')
            ->distinct()
            ->orderBy('destination')
            ->pluck('destination');

        return view('distribution', compact(
            'distributions',
            'products',
            'totalSalesAmount',
            'totalUnpaidAmount',
            'topClients',
            'clients'
        ));
    }

    public function exportPdf(Request $request)
    {
        $distributions = $this->filteredQuery($request)
            ->with(['items.product'])
            ->orderBy('distribution_date')
            ->orderBy('id')
            ->get();

        $summary = [
            'orders' => $distributions->count(),
            'quantity' => $distributions->sum('qty_out'),
            'sales' => $distributions->sum('total_amount'),
            'paid' => $distributions->sum('paid_amount'),
            'unpaid' => $distributions->sum(
                fn ($item) => max((float) $item->total_amount - (float) $item->paid_amount, 0)
            ),
        ];

        $filters = $request->only(['client', 'date_from', 'date_to', 'payment_status']);
        $pdf = Pdf::loadView(
            'distribution-pdf',
            compact('distributions', 'summary', 'filters')
        )->setPaper('a4', 'landscape');

        $clientName = $request->filled('client')
            ? Str::slug($request->client)
            : 'all-clients';

        return $pdf->download("distribution-{$clientName}.pdf");
    }

    public function exportInvoice(Distribution $distribution)
    {
        $distribution->load(['items.product']);
        $items = $this->displayItems($distribution);
        $subtotal = $items->sum('subtotal');
        $outstanding = max(
            (float) $distribution->total_amount - (float) $distribution->paid_amount,
            0
        );
        $amountInWords = ucfirst($this->spellNumber((int) round($distribution->total_amount))) . ' rupiah';
        $logoPath = public_path('images/LOGO HIJAU.png');
        $logoData = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('distribution-invoice', compact(
            'distribution',
            'items',
            'subtotal',
            'outstanding',
            'amountInWords',
            'logoData'
        ))->setPaper('a4', 'portrait');

        return $pdf->download(($distribution->invoice_number ?: "invoice-{$distribution->id}") . '.pdf');
    }

    public function store(Request $request)
    {
        $this->normalizeLegacyItem($request);
        $validated = $this->validateDistribution($request);
        $amounts = $this->calculateAmounts($validated);

        DB::transaction(function () use ($validated, $amounts) {
            $products = $this->lockProducts($validated['items']);
            $itemData = $this->prepareItemsAndDeductStock($validated['items'], $products);

            $distribution = Distribution::create(
                $this->distributionData($validated, $amounts, $itemData)
            );
            $distribution->update([
                'invoice_number' => $this->invoiceNumber(
                    $distribution->id,
                    $validated['distribution_date']
                ),
            ]);
            $distribution->items()->createMany($itemData);

            ActivityLog::create([
                'user_name' => auth()->user()->name,
                'activity' => sprintf(
                    'Created distribution %s: %s item(s), %s pcs to %s (%s)',
                    $distribution->invoice_number,
                    count($itemData),
                    collect($itemData)->sum('qty_out'),
                    $validated['destination'],
                    $validated['payment_status']
                ),
            ]);
        });

        return redirect('/distribution')->with('success', 'Distribution created.');
    }

    public function edit(Distribution $distribution)
    {
        abort(403, 'Distribution transactions cannot be edited for audit safety.');
    }

    public function update(Request $request, Distribution $distribution)
    {
        abort(403, 'Distribution transactions cannot be edited for audit safety.');
    }

    public function destroy(Distribution $distribution)
    {
        abort(403, 'Distribution transactions cannot be deleted for audit safety.');
    }

    private function validateDistribution(Request $request): array
    {
        return $request->validate([
            'distribution_date' => ['required', 'date'],
            'destination' => ['required', 'string', 'max:255'],
            'client_phone' => ['required', 'regex:/^[0-9]{10,13}$/'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.qty_out' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'payment_status' => ['required', Rule::in(['Unpaid', 'Partial', 'Paid'])],
            'payment_method' => ['required', Rule::in(['Cash', 'Transfer', 'Credit', 'Other'])],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
        ], [
            'client_phone.required' => 'Client phone is required.',
            'client_phone.regex' => 'Client phone must contain 10 to 13 digits.',
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        return Distribution::query()
            ->when($request->filled('client'), fn ($query) =>
                $query->where('destination', $request->client)
            )
            ->when($request->filled('date_from'), fn ($query) =>
                $query->whereDate('distribution_date', '>=', $request->date_from)
            )
            ->when($request->filled('date_to'), fn ($query) =>
                $query->whereDate('distribution_date', '<=', $request->date_to)
            )
            ->when($request->filled('payment_status'), fn ($query) =>
                $query->where('payment_status', $request->payment_status)
            );
    }

    private function calculateAmounts(array $validated): array
    {
        $subtotal = collect($validated['items'])->sum(
            fn ($item) => (int) $item['qty_out'] * (float) $item['price']
        );
        $shippingCost = (float) ($validated['shipping_cost'] ?? 0);
        $tax = (float) ($validated['tax'] ?? 0);
        $totalAmount = $subtotal + $shippingCost + $tax;
        $paidAmount = (float) ($validated['paid_amount'] ?? 0);

        if ($validated['payment_status'] === 'Unpaid') {
            $paidAmount = 0;
        } elseif ($validated['payment_status'] === 'Paid') {
            $paidAmount = $totalAmount;
        } elseif ($paidAmount <= 0 || $paidAmount >= $totalAmount) {
            throw ValidationException::withMessages([
                'paid_amount' => 'Partial payment must be greater than Rp0 and lower than the total amount.',
            ]);
        }

        return compact('subtotal', 'shippingCost', 'tax', 'totalAmount', 'paidAmount');
    }

    private function distributionData(array $validated, array $amounts, array $items): array
    {
        $itemCount = count($items);
        $firstItem = $items[0];

        return [
            'distribution_date' => $validated['distribution_date'],
            'product_name' => $itemCount === 1 ? $firstItem['product_name'] : "{$itemCount} products",
            'qty_out' => collect($items)->sum('qty_out'),
            'price' => $itemCount === 1 ? $firstItem['price'] : 0,
            'shipping_cost' => $amounts['shippingCost'],
            'tax' => $amounts['tax'],
            'total_amount' => $amounts['totalAmount'],
            'destination' => $validated['destination'],
            'client_phone' => $validated['client_phone'] ?? null,
            'note' => $validated['note'] ?? null,
            'payment_status' => $validated['payment_status'],
            'payment_method' => $validated['payment_method'],
            'paid_amount' => $amounts['paidAmount'],
        ];
    }

    private function lockProducts(array $items): Collection
    {
        return Product::whereIn('id', collect($items)->pluck('product_id')->unique()->sort())
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function prepareItemsAndDeductStock(
        array $items,
        Collection $products,
        bool $saveImmediately = true
    ): array {
        $prepared = [];

        foreach ($items as $item) {
            $product = $products->get((int) $item['product_id']);
            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => 'One of the selected products was not found.',
                ]);
            }
            if ($product->stock < $item['qty_out']) {
                throw ValidationException::withMessages([
                    'stock' => sprintf(
                        '%s has insufficient stock. Required %s pcs, available %s pcs.',
                        $product->product_name,
                        number_format((int) $item['qty_out'], 0, '.', ','),
                        number_format((float) $product->stock, 0, '.', ',')
                    ),
                ]);
            }

            $product->stock -= $item['qty_out'];
            if ($saveImmediately) {
                $product->save();
            }

            $prepared[] = [
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'unit' => $product->unit ?: 'pcs',
                'qty_out' => (int) $item['qty_out'],
                'price' => (float) $item['price'],
                'subtotal' => (int) $item['qty_out'] * (float) $item['price'],
            ];
        }

        return $prepared;
    }

    private function normalizeLegacyItem(Request $request): void
    {
        if ($request->has('items') || ! $request->filled('product_name')) {
            return;
        }

        $product = Product::where('product_name', $request->product_name)->first();
        $request->merge([
            'items' => [[
                'product_id' => $product?->id,
                'qty_out' => $request->qty_out,
                'price' => $request->price,
            ]],
            'payment_method' => $request->input('payment_method', 'Transfer'),
        ]);
    }

    private function displayItems(Distribution $distribution): Collection
    {
        if ($distribution->items->isNotEmpty()) {
            return $distribution->items;
        }

        $product = Product::where('product_name', $distribution->product_name)->first();

        return collect([(object) [
            'product_id' => $product?->id,
            'product_name' => $distribution->product_name,
            'unit' => $product?->unit ?? 'pcs',
            'qty_out' => $distribution->qty_out,
            'price' => $distribution->price,
            'subtotal' => $distribution->qty_out * $distribution->price,
        ]]);
    }

    private function invoiceNumber(int $id, string $date): string
    {
        return sprintf('INV-%s-%04d', str_replace('-', '', $date), $id);
    }

    private function spellNumber(int $number): string
    {
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($number < 12) {
            return $words[$number];
        }

        if ($number < 20) {
            return trim($this->spellNumber($number - 10) . ' belas');
        }

        if ($number < 100) {
            return trim($this->spellNumber(intdiv($number, 10)) . ' puluh ' . $this->spellNumber($number % 10));
        }

        if ($number < 200) {
            return trim('seratus ' . $this->spellNumber($number - 100));
        }

        if ($number < 1000) {
            return trim($this->spellNumber(intdiv($number, 100)) . ' ratus ' . $this->spellNumber($number % 100));
        }

        if ($number < 2000) {
            return trim('seribu ' . $this->spellNumber($number - 1000));
        }

        if ($number < 1000000) {
            return trim($this->spellNumber(intdiv($number, 1000)) . ' ribu ' . $this->spellNumber($number % 1000));
        }

        if ($number < 1000000000) {
            return trim($this->spellNumber(intdiv($number, 1000000)) . ' juta ' . $this->spellNumber($number % 1000000));
        }

        return trim($this->spellNumber(intdiv($number, 1000000000)) . ' miliar ' . $this->spellNumber($number % 1000000000));
    }
}