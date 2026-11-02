<?php

namespace Tests\Feature\Transaction;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_transaksi_menampilkan_data(): void
    {
        $this->actingAs(User::factory()->create());

        $account = $this->account();
        Transaction::create([
            'date' => '2022-01-01',
            'chart_of_account_id' => $account->id,
            'description' => 'Gaji Di Perusahaan A',
            'debit' => '0.00',
            'credit' => '5000000.00',
        ]);

        $this->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('2022-01-01')
            ->assertSee('Gaji Di Perusahaan A')
            ->assertSee('401 - Gaji Karyawan')
            ->assertSee('Salary')
            ->assertSee('5.000.000,00');
    }

    public function test_form_tambah_dan_ubah_dapat_dibuka(): void
    {
        $this->actingAs(User::factory()->create());
        $transaction = $this->transaction();

        $this->get(route('transactions.create'))->assertOk()->assertSee('Tambah transaksi');
        $this->get(route('transactions.edit', $transaction))->assertOk()->assertSee('Gaji Januari');
    }

    public function test_tanggal_wajib_diisi(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['date' => '']))
            ->assertSessionHasErrors('date');
    }

    public function test_tanggal_harus_format_y_m_d(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['01/10/2022', '2022-1-10', '10-01-2022'] as $date) {
            $this->post(route('transactions.store'), $this->payload(['date' => $date]))
                ->assertSessionHasErrors('date');
        }
    }

    public function test_coa_wajib_dan_harus_ada(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['chart_of_account_id' => '']))
            ->assertSessionHasErrors('chart_of_account_id');

        $this->post(route('transactions.store'), $this->payload(['chart_of_account_id' => 99999]))
            ->assertSessionHasErrors('chart_of_account_id');
    }

    public function test_deskripsi_maksimal_dua_ratus_lima_puluh_lima_karakter(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['description' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('description');
    }

    public function test_debit_dan_credit_harus_numeric(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['credit' => 'abc']))
            ->assertSessionHasErrors('credit');

        $this->post(route('transactions.store'), $this->payload(['credit' => '0', 'debit' => 'abc']))
            ->assertSessionHasErrors('debit');
    }

    public function test_nominal_negatif_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['credit' => '-1']))
            ->assertSessionHasErrors('credit');
    }

    public function test_nominal_melebihi_batas_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['credit' => '10000000000000']))
            ->assertSessionHasErrors('credit');

        $this->post(route('transactions.store'), $this->payload(['credit' => '9999999999999.99']))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHasNoErrors();
    }

    public function test_nominal_maksimal_dua_desimal(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['credit' => '100.555']))
            ->assertSessionHasErrors('credit');
    }

    public function test_debit_dan_credit_tidak_boleh_keduanya_nol(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['debit' => '', 'credit' => '']))
            ->assertSessionHasErrors(['debit', 'credit']);
    }

    public function test_debit_dan_credit_tidak_boleh_keduanya_terisi(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['debit' => '100', 'credit' => '200']))
            ->assertSessionHasErrors(['debit', 'credit']);
    }

    public function test_input_kosong_dinormalisasi_jadi_nol(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['debit' => '', 'credit' => '1500.5']))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHasNoErrors();

        $transaction = Transaction::query()->sole();

        $this->assertSame('0.00', $transaction->debit);
        $this->assertSame('1500.50', $transaction->credit);
    }

    public function test_store_menyimpan_transaksi(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('transactions.store'), $this->payload(['debit' => '25000', 'credit' => '0']))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('transactions', [
            'date' => '2022-01-10 00:00:00',
            'description' => 'Gaji Januari',
            'debit' => 25000,
        ]);
    }

    public function test_update_menyimpan_perubahan(): void
    {
        $this->actingAs(User::factory()->create());
        $transaction = $this->transaction();

        $this->put(route('transactions.update', $transaction), $this->payload([
            'description' => 'Gaji Januari direvisi',
            'debit' => '1000.00',
            'credit' => '0',
        ]))->assertRedirect(route('transactions.index'))
            ->assertSessionHas('status');

        $transaction->refresh();

        $this->assertSame('Gaji Januari direvisi', $transaction->description);
        $this->assertSame('1000.00', $transaction->debit);
        $this->assertSame('0.00', $transaction->credit);
    }

    public function test_hapus_transaksi(): void
    {
        $this->actingAs(User::factory()->create());
        $transaction = $this->transaction();

        $this->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_deskripsi_berisi_script_ter_escape_di_daftar(): void
    {
        $this->actingAs(User::factory()->create());

        Transaction::create([
            'date' => '2022-01-01',
            'chart_of_account_id' => $this->account()->id,
            'description' => '<script>alert("tx")</script>',
            'debit' => '0.00',
            'credit' => '100.00',
        ]);

        $this->get(route('transactions.index'))
            ->assertOk()
            ->assertDontSee('<script>alert("tx")</script>', false)
            ->assertSee('&lt;script&gt;alert(&quot;tx&quot;)&lt;/script&gt;', false);
    }

    public function test_semua_route_transaksi_butuh_login(): void
    {
        $transaction = $this->transaction();

        $routes = [
            ['get', route('transactions.index')],
            ['get', route('transactions.create')],
            ['post', route('transactions.store')],
            ['get', route('transactions.edit', $transaction)],
            ['put', route('transactions.update', $transaction)],
            ['delete', route('transactions.destroy', $transaction)],
        ];

        foreach ($routes as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('login'));
        }
    }

    private function account(): ChartOfAccount
    {
        $category = Category::firstOrCreate(
            ['name' => 'Salary'],
            ['type' => Category::TYPE_INCOME],
        );

        return ChartOfAccount::create([
            'code' => '401',
            'name' => 'Gaji Karyawan',
            'category_id' => $category->id,
        ]);
    }

    private function transaction(): Transaction
    {
        return Transaction::create([
            'date' => '2022-01-01',
            'chart_of_account_id' => $this->account()->id,
            'description' => 'Gaji Januari',
            'debit' => '0.00',
            'credit' => '5000000.00',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $account = ChartOfAccount::query()->first() ?? $this->account();

        return array_merge([
            'date' => '2022-01-10',
            'chart_of_account_id' => $account->id,
            'description' => 'Gaji Januari',
            'debit' => '0',
            'credit' => '5000000',
        ], $overrides);
    }
}
