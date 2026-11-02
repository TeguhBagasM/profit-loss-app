<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_rentang_tanggal_inklusif(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->account();

        $this->transaction($account, '2021-12-31', 'Akhir tahun lalu');
        $this->transaction($account, '2022-01-01', 'Batas awal');
        $this->transaction($account, '2022-01-15', 'Batas akhir');
        $this->transaction($account, '2022-02-01', 'Di luar rentang');

        $this->get(route('transactions.index', ['date_from' => '2022-01-01', 'date_to' => '2022-01-15']))
            ->assertOk()
            ->assertSee('Batas awal')
            ->assertSee('Batas akhir')
            ->assertDontSee('Akhir tahun lalu')
            ->assertDontSee('Di luar rentang');
    }

    public function test_filter_coa(): void
    {
        $this->actingAs(User::factory()->create());

        $salary = $this->account();
        $transport = $this->account('602', 'Bensin', 'Transport Expense', Category::TYPE_EXPENSE);

        $this->transaction($salary, '2022-01-01', 'Gaji Januari');
        $this->transaction($transport, '2022-01-02', 'Bensin Anak');

        $this->get(route('transactions.index', ['chart_of_account_id' => $transport->id]))
            ->assertOk()
            ->assertSee('Bensin Anak')
            ->assertDontSee('Gaji Januari');
    }

    public function test_filter_kategori(): void
    {
        $this->actingAs(User::factory()->create());

        $salary = $this->account();
        $transport = $this->account('602', 'Bensin', 'Transport Expense', Category::TYPE_EXPENSE);

        $this->transaction($salary, '2022-01-01', 'Gaji Januari');
        $this->transaction($transport, '2022-01-02', 'Bensin Anak');

        $this->get(route('transactions.index', ['category_id' => $transport->category_id]))
            ->assertOk()
            ->assertSee('Bensin Anak')
            ->assertDontSee('Gaji Januari');
    }

    public function test_pencarian_deskripsi_meng_escape_persen_dan_underscore(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->account();

        $this->transaction($account, '2022-01-01', 'Diskon 50% Bulan Ini');
        $this->transaction($account, '2022-01-02', 'Kode_A Promo');
        $this->transaction($account, '2022-01-03', 'Gaji Januari');

        $this->get(route('transactions.index', ['search' => '%']))
            ->assertOk()
            ->assertSee('Diskon 50% Bulan Ini')
            ->assertDontSee('Gaji Januari')
            ->assertDontSee('Kode_A Promo');

        $this->get(route('transactions.index', ['search' => '50%']))
            ->assertOk()
            ->assertSee('Diskon 50% Bulan Ini')
            ->assertDontSee('Gaji Januari');

        $this->get(route('transactions.index', ['search' => '_']))
            ->assertOk()
            ->assertSee('Kode_A Promo')
            ->assertDontSee('Gaji Januari')
            ->assertDontSee('Diskon 50% Bulan Ini');
    }

    public function test_urutan_default_tanggal_terbaru_dulu(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->account();

        $this->transaction($account, '2022-01-01', 'Lebih lama');
        $newest = $this->transaction($account, '2022-03-01', 'Terbaru');

        $response = $this->get(route('transactions.index'))->assertOk();

        $this->assertSame($newest->id, $response->viewData('transactions')->first()->id);
    }

    public function test_sort_dari_whitelist_bekerja(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->account();

        $this->transaction($account, '2022-01-01', 'Kecil', debit: '10.00');
        $this->transaction($account, '2022-01-02', 'Besar', debit: '500.00');
        $biggest = $this->transaction($account, '2022-01-03', 'Paling besar', debit: '900.00');

        $response = $this->get(route('transactions.index', ['sort' => 'debit', 'direction' => 'desc']))
            ->assertOk();

        $this->assertSame($biggest->id, $response->viewData('transactions')->first()->id);
    }

    public function test_sort_di_luar_whitelist_diabaikan_tanpa_error(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->account();

        $this->transaction($account, '2022-01-01', 'Lebih lama');
        $newest = $this->transaction($account, '2022-03-01', 'Terbaru');

        $response = $this->get(route('transactions.index', ['sort' => 'password', 'direction' => 'desc']))
            ->assertOk();

        // Nilai tidak dikenal diabaikan, urutan kembali ke default.
        $this->assertSame($newest->id, $response->viewData('transactions')->first()->id);
    }

    public function test_paginasi_lima_belas_dan_query_string_dipertahankan(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->account();

        foreach (range(1, 20) as $index) {
            $this->transaction($account, '2022-01-01', 'Gaji '.$index);
        }

        $response = $this->get(route('transactions.index', ['search' => 'Gaji']))->assertOk();

        $paginator = $response->viewData('transactions');

        $this->assertSame(15, $paginator->count());
        $this->assertSame(20, $paginator->total());
        $this->assertStringContainsString('search=Gaji', $paginator->url(2));
    }

    public function test_daftar_transaksi_tanpa_n_plus_one(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->account();

        foreach (range(1, 3) as $index) {
            $this->transaction($account, '2022-01-01', 'Transaksi '.$index);
        }

        $fewQueries = $this->countIndexQueries();

        foreach (range(4, 20) as $index) {
            $this->transaction($account, '2022-01-01', 'Transaksi '.$index);
        }

        $manyQueries = $this->countIndexQueries();

        $this->assertSame(
            $fewQueries,
            $manyQueries,
            "Jumlah query berubah saat data bertambah ({$fewQueries} vs {$manyQueries}); kemungkinan N+1.",
        );
        $this->assertLessThanOrEqual(8, $manyQueries);
    }

    public function test_filter_tanggal_dengan_format_salah_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('transactions.index', ['date_from' => '01/01/2022']))
            ->assertSessionHasErrors('date_from');
    }

    private function account(
        string $code = '401',
        string $name = 'Gaji Karyawan',
        string $categoryName = 'Salary',
        string $categoryType = Category::TYPE_INCOME,
    ): ChartOfAccount {
        $category = Category::firstOrCreate(
            ['name' => $categoryName],
            ['type' => $categoryType],
        );

        return ChartOfAccount::create([
            'code' => $code,
            'name' => $name,
            'category_id' => $category->id,
        ]);
    }

    private function transaction(
        ChartOfAccount $account,
        string $date,
        string $description,
        string $debit = '0.00',
        string $credit = '0.00',
    ): Transaction {
        if ($debit === '0.00' && $credit === '0.00') {
            $credit = '100.00';
        }

        return Transaction::create([
            'date' => $date,
            'chart_of_account_id' => $account->id,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
        ]);
    }

    private function countIndexQueries(): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('transactions.index'))->assertOk();

        $count = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $count;
    }
}
