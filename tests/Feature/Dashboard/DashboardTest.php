<?php

namespace Tests\Feature\Dashboard;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_butuh_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_menampilkan_ringkasan_bulan_transaksi_terbaru(): void
    {
        $this->seed();
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('2022-01')
            ->assertSee('12.000.000,00')
            ->assertSee('25.000,00')
            ->assertSee('11.975.000,00')
            ->assertSee('Gaji Di Persuhaan A');
    }

    public function test_menampilkan_lima_transaksi_terakhir_saja(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::firstOrCreate(
            ['name' => 'Salary'],
            ['type' => Category::TYPE_INCOME],
        );

        $account = ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);

        foreach (range(1, 7) as $day) {
            Transaction::create([
                'date' => sprintf('2022-01-%02d', $day),
                'chart_of_account_id' => $account->id,
                'description' => 'Catatan hari ke-'.$day,
                'debit' => '0.00',
                'credit' => '100.00',
            ]);
        }

        $response = $this->get(route('dashboard'))->assertOk();

        $transactions = $response->viewData('transactions');

        $this->assertCount(5, $transactions);
        $this->assertSame('2022-01-07', $transactions->first()->date->format('Y-m-d'));

        $response->assertDontSee('Catatan hari ke-1')
            ->assertDontSee('Catatan hari ke-2');
    }

    public function test_menampilkan_empty_state_tanpa_transaksi(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada transaksi')
            ->assertDontSee('Total income')
            ->assertDontSee('5 transaksi terakhir');
    }

    public function test_ringkasan_memakai_bulan_transaksi_terbaru_bukan_bulan_berjalan(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::firstOrCreate(
            ['name' => 'Salary'],
            ['type' => Category::TYPE_INCOME],
        );

        $account = ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);

        Transaction::create([
            'date' => '2021-06-15',
            'chart_of_account_id' => $account->id,
            'description' => 'Gaji lama',
            'debit' => '0.00',
            'credit' => '1000.00',
        ]);

        Transaction::create([
            'date' => '2021-08-15',
            'chart_of_account_id' => $account->id,
            'description' => 'Gaji terbaru',
            'debit' => '0.00',
            'credit' => '2500.00',
        ]);

        $response = $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan 2021-08')
            ->assertSee('2.500,00');

        // Ringkasan hanya memakai bulan transaksi terbaru (Agustus 2021),
        // bukan bulan berjalan maupun bulan transaksi paling lama.
        $this->assertSame(['2021-08'], $response->viewData('report')->periods);
    }
}
