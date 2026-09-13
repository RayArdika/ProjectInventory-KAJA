<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OperationalDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProducts();
        $this->seedMaterials();
    }

    private function seedMaterials(): void
    {
        $relevantMaterialNames = collect([
            'Kantong Teh Polos Personal',
            'Sticker Kantong Yellow',
            'Sticker Kantong Reddish',
            'Sticker Kantong Simple',
            'Yellow Helow Bubuk',
            'Reddish Wish Bubuk',
            'Simple Purple Bubuk',
        ])->map(fn ($name) => strtolower($name))->all();

        foreach ($this->readCsv('operational_inventory_clean.csv') as $row) {
            if (($row['category'] ?? '') === 'STOCK JADI') {
                continue;
            }

            if (! in_array(strtolower(trim($row['name'])), $relevantMaterialNames, true)) {
                continue;
            }

            Material::updateOrCreate(
                ['material_name' => $row['name']],
                [
                    'category' => $row['category'],
                    'unit' => 'pcs',
                    'stock' => floor($this->number($row['stock'])),
                    'minimum_stock' => 0,
                    'price_per_unit' => $this->number($row['price_per_unit']),
                    'selling_price' => $this->number($row['selling_price']),
                ]
            );
        }
    }

    private function seedProducts(): void
    {
        $sequence = 1;

        foreach ($this->readCsv('operational_products_finished_goods.csv') as $row) {
            if (! preg_match('/^(Kantong|Sachet)\s+/i', trim((string) $row['name']))) {
                continue;
            }

            Product::updateOrCreate(
                ['product_name' => $row['name']],
                [
                    'sku' => str_pad((string) $sequence++, 3, '0', STR_PAD_LEFT),
                    'category' => $row['category'],
                    'unit' => $row['unit'],
                    'stock' => $this->number($row['stock']),
                    'worker_fee' => 0,
                    'minimum_stock' => 0,
                    'warning_stock' => 20,
                    'price_per_unit' => $this->number($row['price_per_unit']),
                    'selling_price' => $this->number($row['selling_price']),
                ]
            );
        }
    }

    private function readCsv(string $filename): array
    {
        $path = database_path('seeders/data/' . $filename);
        $handle = fopen($path, 'r');
        $headers = array_map(
            fn ($header) => trim((string) $header, "\xEF\xBB\xBF \t\n\r\0\x0B"),
            fgetcsv($handle)
        );
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    private function number($value): float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return 0;
        }

        return (float) $value;
    }
}
