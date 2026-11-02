<?php

namespace Tests\Feature\ChartOfAccount;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChartOfAccountCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_coa_menampilkan_kode_nama_dan_kategori(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);
        ChartOfAccount::create(['code' => '401', 'name' => 'Gaji Karyawan', 'category_id' => $category->id]);

        $this->get(route('chart-of-accounts.index'))
            ->assertOk()
            ->assertSee('401')
            ->assertSee('Gaji Karyawan')
            ->assertSee('Salary');
    }

    public function test_kode_wajib_diisi(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('chart-of-accounts.store'), [
            'code' => '',
            'name' => 'Gaji',
            'category_id' => $this->category()->id,
        ])->assertSessionHasErrors('code');
    }

    public function test_kode_harus_sesuai_regex(): void
    {
        $this->actingAs(User::factory()->create());
        $category = $this->category();

        foreach (['kode salah', 'kode#salah', str_repeat('a', 21)] as $code) {
            $this->post(route('chart-of-accounts.store'), [
                'code' => $code,
                'name' => 'Gaji',
                'category_id' => $category->id,
            ])->assertSessionHasErrors('code');
        }
    }

    public function test_kode_unik(): void
    {
        $this->actingAs(User::factory()->create());
        $category = $this->category();
        ChartOfAccount::create(['code' => '401', 'name' => 'Gaji', 'category_id' => $category->id]);

        $this->post(route('chart-of-accounts.store'), [
            'code' => '401',
            'name' => 'Gaji Lain',
            'category_id' => $category->id,
        ])->assertSessionHasErrors('code');
    }

    public function test_unique_saat_update_mengabaikan_dirinya_sendiri(): void
    {
        $this->actingAs(User::factory()->create());
        $category = $this->category();
        $account = ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji',
            'category_id' => $category->id,
        ]);

        $this->put(route('chart-of-accounts.update', $account), [
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ])->assertRedirect(route('chart-of-accounts.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id, 'name' => 'Gaji Karyawan']);
    }

    public function test_nama_maksimal_seratus_lima_puluh_karakter(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('chart-of-accounts.store'), [
            'code' => '401',
            'name' => str_repeat('a', 151),
            'category_id' => $this->category()->id,
        ])->assertSessionHasErrors('name');
    }

    public function test_kategori_wajib_ada(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('chart-of-accounts.store'), [
            'code' => '401',
            'name' => 'Gaji',
            'category_id' => 99999,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_store_menyimpan_coa(): void
    {
        $this->actingAs(User::factory()->create());
        $category = $this->category();

        $this->post(route('chart-of-accounts.store'), [
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ])->assertRedirect(route('chart-of-accounts.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('chart_of_accounts', ['code' => '401', 'name' => 'Gaji Karyawan']);
    }

    public function test_hapus_coa_yang_punya_transaksi_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        $account = $this->accountWithTransaction();

        $this->from(route('chart-of-accounts.index'))
            ->delete(route('chart-of-accounts.destroy', $account))
            ->assertRedirect(route('chart-of-accounts.index'))
            ->assertSessionHas('error', fn (string $message): bool => str_contains($message, '1 transaksi'));

        $this->assertDatabaseHas('chart_of_accounts', ['id' => $account->id]);
    }

    public function test_hapus_coa_tanpa_transaksi_berhasil(): void
    {
        $this->actingAs(User::factory()->create());
        $account = ChartOfAccount::create([
            'code' => '409',
            'name' => 'Tanpa Transaksi',
            'category_id' => $this->category()->id,
        ]);

        $this->delete(route('chart-of-accounts.destroy', $account))
            ->assertRedirect(route('chart-of-accounts.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('chart_of_accounts', ['id' => $account->id]);
    }

    public function test_pencarian_meng_escape_persen(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seedAccounts();

        $this->get(route('chart-of-accounts.index', ['search' => '%']))
            ->assertOk()
            ->assertSee('Diskon 50% Spesial')
            ->assertDontSee('Makan Siang')
            ->assertDontSee('Kode_A');

        $this->get(route('chart-of-accounts.index', ['search' => '50%']))
            ->assertOk()
            ->assertSee('Diskon 50% Spesial')
            ->assertDontSee('Makan Siang');
    }

    public function test_pencarian_meng_escape_underscore(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seedAccounts();

        $this->get(route('chart-of-accounts.index', ['search' => '_']))
            ->assertOk()
            ->assertSee('Kode_A')
            ->assertDontSee('Makan Siang')
            ->assertDontSee('Diskon 50% Spesial');
    }

    public function test_pencarian_biasa_berdasarkan_kode_dan_nama(): void
    {
        $this->actingAs(User::factory()->create());
        $this->seedAccounts();

        $this->get(route('chart-of-accounts.index', ['search' => 'Makan']))
            ->assertOk()
            ->assertSee('Makan Siang')
            ->assertDontSee('Kode_A');

        $this->get(route('chart-of-accounts.index', ['search' => '610']))
            ->assertOk()
            ->assertSee('Diskon 50% Spesial')
            ->assertDontSee('Makan Siang');
    }

    public function test_filter_kategori(): void
    {
        $this->actingAs(User::factory()->create());

        $meal = Category::create(['name' => 'Meal Expense', 'type' => Category::TYPE_EXPENSE]);
        $transport = Category::create(['name' => 'Transport Expense', 'type' => Category::TYPE_EXPENSE]);

        ChartOfAccount::create(['code' => '604', 'name' => 'Makan Siang', 'category_id' => $meal->id]);
        ChartOfAccount::create(['code' => '602', 'name' => 'Bensin', 'category_id' => $transport->id]);

        $this->get(route('chart-of-accounts.index', ['category_id' => $meal->id]))
            ->assertOk()
            ->assertSee('Makan Siang')
            ->assertDontSee('Bensin');
    }

    public function test_paginasi_lima_belas_per_halaman(): void
    {
        $this->actingAs(User::factory()->create());
        $category = $this->category();

        foreach (range(1, 20) as $index) {
            ChartOfAccount::create([
                'code' => sprintf('60%02d', $index),
                'name' => 'COA '.$index,
                'category_id' => $category->id,
            ]);
        }

        $response = $this->get(route('chart-of-accounts.index'))->assertOk();

        $paginator = $response->viewData('chartOfAccounts');

        $this->assertSame(15, $paginator->count());
        $this->assertSame(20, $paginator->total());
    }

    public function test_daftar_coa_tanpa_n_plus_one(): void
    {
        $this->actingAs(User::factory()->create());
        $category = $this->category();

        foreach (range(1, 3) as $index) {
            ChartOfAccount::create([
                'code' => sprintf('70%02d', $index),
                'name' => 'COA '.$index,
                'category_id' => $category->id,
            ]);
        }

        $fewQueries = $this->countIndexQueries();

        foreach (range(4, 20) as $index) {
            ChartOfAccount::create([
                'code' => sprintf('70%02d', $index),
                'name' => 'COA '.$index,
                'category_id' => $category->id,
            ]);
        }

        $manyQueries = $this->countIndexQueries();

        $this->assertSame(
            $fewQueries,
            $manyQueries,
            "Jumlah query berubah saat data bertambah ({$fewQueries} vs {$manyQueries}); kemungkinan N+1.",
        );
        $this->assertLessThanOrEqual(5, $manyQueries);
    }

    public function test_nama_berisi_script_ter_escape_di_daftar(): void
    {
        $this->actingAs(User::factory()->create());

        ChartOfAccount::create([
            'code' => '401',
            'name' => '<script>alert("coa")</script>',
            'category_id' => $this->category()->id,
        ]);

        $this->get(route('chart-of-accounts.index'))
            ->assertOk()
            ->assertDontSee('<script>alert("coa")</script>', false)
            ->assertSee('&lt;script&gt;alert(&quot;coa&quot;)&lt;/script&gt;', false);
    }

    public function test_semua_route_coa_butuh_login(): void
    {
        $account = ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji',
            'category_id' => $this->category()->id,
        ]);

        $routes = [
            ['get', route('chart-of-accounts.index')],
            ['get', route('chart-of-accounts.create')],
            ['post', route('chart-of-accounts.store')],
            ['get', route('chart-of-accounts.edit', $account)],
            ['put', route('chart-of-accounts.update', $account)],
            ['delete', route('chart-of-accounts.destroy', $account)],
        ];

        foreach ($routes as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('login'));
        }
    }

    private function category(): Category
    {
        return Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);
    }

    private function accountWithTransaction(): ChartOfAccount
    {
        $account = ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $this->category()->id,
        ]);

        Transaction::create([
            'date' => '2022-01-01',
            'chart_of_account_id' => $account->id,
            'description' => 'gaji',
            'debit' => '0.00',
            'credit' => '5000000.00',
        ]);

        return $account;
    }

    private function seedAccounts(): void
    {
        $category = Category::create(['name' => 'Meal Expense', 'type' => Category::TYPE_EXPENSE]);

        ChartOfAccount::create(['code' => '604', 'name' => 'Makan Siang', 'category_id' => $category->id]);
        ChartOfAccount::create(['code' => '610', 'name' => 'Diskon 50% Spesial', 'category_id' => $category->id]);
        ChartOfAccount::create(['code' => '611', 'name' => 'Kode_A', 'category_id' => $category->id]);
    }

    private function countIndexQueries(): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('chart-of-accounts.index'))->assertOk();

        $count = count(DB::getQueryLog());

        DB::disableQueryLog();

        return $count;
    }
}
