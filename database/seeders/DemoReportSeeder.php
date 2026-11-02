<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

/**
 * Data demo untuk mereproduksi angka contoh laporan pada soal.
 *
 * Tidak dipanggil oleh DatabaseSeeder. Jalankan manual:
 *   php artisan db:seed --class=DemoReportSeeder
 *
 * Seeder ini MEGGANTI transaksi Januari-Maret 2022 agar angkanya persis sama
 * dengan contoh (data seeder utama pada bulan itu akan terhapus).
 */
class DemoReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ChartOfAccountSeeder::class,
        ]);

        Transaction::query()
            ->where('date', '>=', '2022-01-01')
            ->where('date', '<=', '2022-03-31 23:59:59')
            ->delete();

        $accountIds = ChartOfAccount::query()->pluck('id', 'code');

        $rows = [
            // COA, tanggal, deskripsi, debit, credit
            ['401', '2022-01-01', 'Gaji Januari', '0.00', '12000000.00'],
            ['401', '2022-02-01', 'Gaji Februari', '0.00', '12000000.00'],
            ['401', '2022-03-01', 'Gaji Maret', '0.00', '12000000.00'],

            ['403', '2022-01-15', 'Pendapatan lain Januari', '0.00', '5500000.00'],
            ['403', '2022-02-15', 'Pendapatan lain Februari', '0.00', '6000000.00'],
            ['403', '2022-03-15', 'Pendapatan lain Maret', '0.00', '3500000.00'],

            ['601', '2022-01-20', 'Biaya keluarga Januari', '500000.00', '0.00'],
            ['601', '2022-02-20', 'Biaya keluarga Februari', '3500000.00', '0.00'],
            ['601', '2022-03-20', 'Biaya keluarga Maret', '4500000.00', '0.00'],

            ['602', '2022-01-25', 'Transportasi Januari', '200000.00', '0.00'],
            ['602', '2022-02-25', 'Transportasi Februari', '250000.00', '0.00'],
            ['602', '2022-03-25', 'Transportasi Maret', '225000.00', '0.00'],

            ['604', '2022-01-28', 'Makan Januari', '150000.00', '0.00'],
            ['604', '2022-02-28', 'Makan Februari', '300000.00', '0.00'],
            ['604', '2022-03-28', 'Makan Maret', '175000.00', '0.00'],
        ];

        foreach ($rows as [$code, $date, $description, $debit, $credit]) {
            Transaction::updateOrCreate(
                [
                    'date' => $date,
                    'chart_of_account_id' => $accountIds[$code],
                    'description' => $description,
                ],
                [
                    'debit' => $debit,
                    'credit' => $credit,
                ],
            );
        }
    }
}
