<?php

namespace Tests\Feature\Database;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_menghasilkan_lima_kategori_dengan_type_benar(): void
    {
        $this->assertDatabaseCount('categories', 5);

        $this->assertSame(2, Category::query()->where('type', Category::TYPE_INCOME)->count());
        $this->assertSame(3, Category::query()->where('type', Category::TYPE_EXPENSE)->count());

        $this->assertDatabaseHas('categories', ['name' => 'Salary', 'type' => 'income']);
        $this->assertDatabaseHas('categories', ['name' => 'Other Income', 'type' => 'income']);
        $this->assertDatabaseHas('categories', ['name' => 'Family Expense', 'type' => 'expense']);
        $this->assertDatabaseHas('categories', ['name' => 'Transport Expense', 'type' => 'expense']);
        $this->assertDatabaseHas('categories', ['name' => 'Meal Expense', 'type' => 'expense']);
    }

    public function test_menghasilkan_delapan_coa_sesuai_soal(): void
    {
        $this->assertDatabaseCount('chart_of_accounts', 8);

        $expected = [
            '401' => ['Gaji Karyawan', 'Salary'],
            '402' => ['Gaji Ketua MPR', 'Salary'],
            '403' => ['Profit Trading', 'Other Income'],
            '601' => ['Biaya Sekolah', 'Family Expense'],
            '602' => ['Bensin', 'Transport Expense'],
            '603' => ['Parkir', 'Transport Expense'],
            '604' => ['Makan Siang', 'Meal Expense'],
            '605' => ['Makana Pokok Bulanan', 'Meal Expense'],
        ];

        foreach ($expected as $code => [$name, $category]) {
            $account = ChartOfAccount::query()->where('code', $code)->firstOrFail();

            $this->assertSame($name, $account->name);
            $this->assertSame($category, $account->category->name);
        }
    }

    public function test_menghasilkan_tiga_transaksi_dengan_debit_credit_benar(): void
    {
        $this->assertDatabaseCount('transactions', 3);

        $transactions = Transaction::query()
            ->with('chartOfAccount')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $rows = $transactions->map(fn (Transaction $transaction): array => [
            $transaction->date->format('Y-m-d'),
            $transaction->chartOfAccount->code,
            $transaction->description,
            $transaction->debit,
            $transaction->credit,
        ])->all();

        $this->assertSame([
            ['2022-01-01', '401', 'Gaji Di Persuhaan A', '0.00', '5000000.00'],
            ['2022-01-02', '402', 'Gaji Ketum', '0.00', '7000000.00'],
            ['2022-01-10', '602', 'Bensin Anak', '25000.00', '0.00'],
        ], $rows);

        // Income = SUM(credit), expense = SUM(debit) atas data seeder.
        $totalIn = Money::add('5000000.00', '7000000.00');
        $totalOut = '25000.00';

        $this->assertSame('12000000.00', $totalIn);
        $this->assertSame('11975000.00', Money::sub($totalIn, $totalOut));
    }

    public function test_menghasilkan_satu_admin_dari_env(): void
    {
        $this->assertDatabaseCount('users', 1);

        $user = User::query()->sole();

        $this->assertSame(env('ADMIN_EMAIL'), $user->email);
        $this->assertNotNull($user->email_verified_at);
    }
}
