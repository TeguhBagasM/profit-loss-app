<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $accountIds = ChartOfAccount::query()->pluck('id', 'code');

        // Tanggal sumber berformat M/D/YYYY pada soal; seeder memakai ISO Y-m-d.
        $transactions = [
            [
                'date' => '2026-01-01',
                'code' => '401',
                'description' => 'Gaji Di Persuhaan A',
                'debit' => '0.00',
                'credit' => '5000000.00',
            ],
            [
                'date' => '2026-01-02',
                'code' => '402',
                'description' => 'Gaji Ketum',
                'debit' => '0.00',
                'credit' => '7000000.00',
            ],
            [
                'date' => '2026-01-10',
                'code' => '602',
                'description' => 'Bensin Anak',
                'debit' => '25000.00',
                'credit' => '0.00',
            ],
        ];

        foreach ($transactions as $transaction) {
            Transaction::updateOrCreate(
                [
                    'date' => $transaction['date'],
                    'chart_of_account_id' => $accountIds[$transaction['code']],
                    'description' => $transaction['description'],
                ],
                [
                    'debit' => $transaction['debit'],
                    'credit' => $transaction['credit'],
                ],
            );
        }
    }
}
