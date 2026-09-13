<?php

namespace Tests\Feature;

use App\Models\Payroll;
use App\Models\Production;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_payroll_pdf_only_contains_their_own_data(): void
    {
        $worker = User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        $this->createPayroll('Siti Aminah', 'Kantong Yellow Helow');
        $this->createPayroll('Bu Dina', 'Kantong Reddish Wish');

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data): bool {
                $this->assertSame('payroll-pdf', $view);
                $this->assertSame(
                    ['Siti Aminah'],
                    $data['payrolls']->pluck('worker_name')->all()
                );

                return true;
            })
            ->andReturnSelf();

        Pdf::shouldReceive('download')
            ->once()
            ->with('laporan-payroll.pdf')
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        $this->actingAs($worker)
            ->get('/payroll/pdf')
            ->assertOk();
    }

    public function test_admin_payroll_pdf_contains_all_workers(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->createPayroll('Siti Aminah', 'Kantong Yellow Helow');
        $this->createPayroll('Bu Dina', 'Kantong Reddish Wish');

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data): bool {
                $this->assertSame('payroll-pdf', $view);
                $this->assertEqualsCanonicalizing(
                    ['Siti Aminah', 'Bu Dina'],
                    $data['payrolls']->pluck('worker_name')->all()
                );

                return true;
            })
            ->andReturnSelf();

        Pdf::shouldReceive('download')
            ->once()
            ->with('laporan-payroll.pdf')
            ->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        $this->actingAs($admin)
            ->get('/payroll/pdf')
            ->assertOk();
    }

    public function test_admin_can_filter_payroll_and_pdf_uses_same_filter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $juneProduction = $this->createProduction(
            'Siti Aminah',
            'Packing Kantong',
            '2026-06-10'
        );
        $mayProduction = $this->createProduction(
            'Bu Dina',
            'Prep Bahan',
            '2026-05-10'
        );

        $this->createPayroll(
            'Siti Aminah',
            'Kantong Yellow Helow',
            $juneProduction->id,
            'Packing Kantong'
        );
        $this->createPayroll(
            'Bu Dina',
            'Kantong Reddish Wish',
            $mayProduction->id,
            'Prep Bahan'
        );

        $query = http_build_query([
            'date_from' => '2026-06-01',
            'date_to' => '2026-06-30',
            'worker' => 'Siti Aminah',
            'activity' => 'Packing Kantong',
        ]);

        $this->actingAs($admin)
            ->get('/payroll?' . $query)
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertSee('2026-06-10')
            ->assertDontSee('Kantong Reddish Wish')
            ->assertSee('Grand Total');

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data): bool {
                $this->assertSame('payroll-pdf', $view);
                $this->assertSame(
                    ['Siti Aminah'],
                    $data['payrolls']->pluck('worker_name')->all()
                );
                $this->assertSame(1000, $data['grandTotal']);

                return true;
            })
            ->andReturnSelf();

        Pdf::shouldReceive('download')
            ->once()
            ->andReturn(response('PDF', 200));

        $this->actingAs($admin)
            ->get('/payroll/pdf?' . $query)
            ->assertOk();
    }

    private function createPayroll(
        string $workerName,
        string $productName,
        ?int $productionId = null,
        string $activityName = 'Packing Kantong'
    ): void
    {
        Payroll::create([
            'production_id' => $productionId,
            'worker_name' => $workerName,
            'product_name' => $productName,
            'activity_name' => $activityName,
            'qty' => 10,
            'unit' => 'pcs',
            'fee' => 100,
            'total_salary' => 1000,
        ]);
    }

    private function createProduction(
        string $workerName,
        string $activityName,
        string $date
    ): Production {
        return Production::create([
            'production_date' => $date,
            'worker_name' => $workerName,
            'activity_name' => $activityName,
            'category' => 'STOCK JADI',
            'product_name' => 'Kantong Test',
            'qty_pass' => 10,
            'qty_reject' => 0,
            'status' => 'Approved',
        ]);
    }
}
