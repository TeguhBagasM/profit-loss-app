<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_kategori_menampilkan_data(): void
    {
        $this->actingAs(User::factory()->create());

        Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Salary');
    }

    public function test_form_tambah_dan_ubah_dapat_dibuka(): void
    {
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);

        $this->get(route('categories.create'))->assertOk()->assertSee('Tambah kategori');
        $this->get(route('categories.edit', $category))->assertOk()->assertSee('Salary');
    }

    public function test_nama_wajib_diisi(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('categories.store'), ['name' => '', 'type' => 'income'])
            ->assertSessionHasErrors('name');
    }

    public function test_nama_maksimal_seratus_karakter(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('categories.store'), [
            'name' => str_repeat('a', 101),
            'type' => 'income',
        ])->assertSessionHasErrors('name');
    }

    public function test_nama_harus_unik(): void
    {
        $this->actingAs(User::factory()->create());
        Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);

        $this->post(route('categories.store'), ['name' => 'Salary', 'type' => 'expense'])
            ->assertSessionHasErrors('name');
    }

    public function test_tipe_harus_income_atau_expense(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('categories.store'), ['name' => 'Bonus', 'type' => 'lainnya'])
            ->assertSessionHasErrors('type');
    }

    public function test_store_menyimpan_kategori(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('categories.store'), ['name' => 'Bonus', 'type' => 'income'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('categories', ['name' => 'Bonus', 'type' => 'income']);
    }

    public function test_unique_saat_update_mengabaikan_dirinya_sendiri(): void
    {
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);

        $this->put(route('categories.update', $category), ['name' => 'Salary', 'type' => 'income'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHasNoErrors();
    }

    public function test_update_nama_bentrok_dengan_kategori_lain_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);
        $category = Category::create(['name' => 'Bonus', 'type' => Category::TYPE_INCOME]);

        $this->put(route('categories.update', $category), ['name' => 'Salary', 'type' => 'income'])
            ->assertSessionHasErrors('name');
    }

    public function test_hapus_kategori_yang_punya_coa_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);
        ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);

        $this->from(route('categories.index'))
            ->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('error', fn (string $message): bool => str_contains($message, '1 COA'));

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_hapus_kategori_tanpa_coa_berhasil(): void
    {
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Bonus', 'type' => Category::TYPE_INCOME]);

        $this->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_ubah_type_ditolak_jika_kategori_punya_transaksi(): void
    {
        $this->actingAs(User::factory()->create());

        $category = Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);
        $account = ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);
        Transaction::create([
            'date' => '2022-01-01',
            'chart_of_account_id' => $account->id,
            'description' => 'gaji',
            'debit' => '0.00',
            'credit' => '5000000.00',
        ]);

        $this->from(route('categories.edit', $category))
            ->put(route('categories.update', $category), ['name' => 'Salary', 'type' => 'expense'])
            ->assertRedirect(route('categories.edit', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'type' => 'income']);
    }

    public function test_ubah_type_diizinkan_jika_belum_ada_transaksi(): void
    {
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Bonus', 'type' => Category::TYPE_INCOME]);

        $this->put(route('categories.update', $category), ['name' => 'Bonus', 'type' => 'expense'])
            ->assertRedirect(route('categories.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'type' => 'expense']);
    }

    public function test_nama_berisi_script_ter_escape_di_daftar(): void
    {
        $this->actingAs(User::factory()->create());

        Category::create([
            'name' => '<script>alert("kategori")</script>',
            'type' => Category::TYPE_INCOME,
        ]);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertDontSee('<script>alert("kategori")</script>', false)
            ->assertSee('&lt;script&gt;alert(&quot;kategori&quot;)&lt;/script&gt;', false);
    }

    public function test_semua_route_kategori_butuh_login(): void
    {
        $category = Category::create(['name' => 'Salary', 'type' => Category::TYPE_INCOME]);

        $routes = [
            ['get', route('categories.index')],
            ['get', route('categories.create')],
            ['post', route('categories.store')],
            ['get', route('categories.edit', $category)],
            ['put', route('categories.update', $category)],
            ['delete', route('categories.destroy', $category)],
        ];

        foreach ($routes as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('login'));
        }
    }
}
