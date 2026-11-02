# Aplikasi Laporan Profit/Loss

Aplikasi keuangan sederhana: kelola kategori, chart of account (COA), dan transaksi,
lalu baca laporan profit/loss per bulan dan ekspor ke Excel.

## Stack

- Laravel 12 (PHP 8.2+), Blade + Alpine.js, Tailwind CSS (versi bawaan Breeze, tanpa upgrade)
- `maatwebsite/excel` 3.1 — versi 4.x menuntut PHP 8.3, sedangkan proyek ini di PHP 8.2
- Seluruh aritmetika uang memakai BCMath lewat `App\Support\Money` (tanpa float)
- Font Hanken Grotesk di-host lokal lewat `@fontsource-variable/hanken-grotesk` (tanpa CDN)

## Instalasi

Prasyarat: PHP 8.2+, Composer 2, Node 20+, ekstensi `bcmath` dan `pdo_sqlite`/`pdo_mysql`.

```bash
composer install
cp .env.example .env
php artisan key:generate

# Default DB_CONNECTION=sqlite
touch database/database.sqlite

# Isi ADMIN_EMAIL dan ADMIN_PASSWORD di .env (lihat bagian Konfigurasi .env)

php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka `http://127.0.0.1:8000`, masuk dengan kredensial seeder.

## Konfigurasi `.env`

| Variabel | Keterangan |
|---|---|
| `ADMIN_EMAIL` | Email user admin yang dibuat `UserSeeder`. Wajib diisi. |
| `ADMIN_PASSWORD` | Password user admin. Wajib diisi di luar environment lokal; fallback `password` hanya dipakai bila `app()->isLocal()`. |
| `APP_DEBUG` | Produksi: `false` agar stack trace tidak bocor. |
| `SESSION_SECURE_COOKIE` | Produksi: `true` (wajib HTTPS). `http_only` dan `same_site=lax` aktif secara default. |

Contoh blok untuk `.env`:

```dotenv
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=password
```

## Kredensial seeder

- Email: nilai `ADMIN_EMAIL` (contoh `admin@example.com`)
- Password: nilai `ADMIN_PASSWORD` (fallback lokal `password`)

Registrasi publik dimatikan: `GET`/`POST /register` mengembalikan 404. Tambah user baru
lewat `UserSeeder` atau `php artisan tinker`.

## Menjalankan test

```bash
php artisan test
# atau
vendor/bin/phpunit
```

Test memakai SQLite in-memory (lihat `phpunit.xml`), jadi tidak menyentuh database lokal.
Pemeriksaan gaya kode: `vendor/bin/pint --test`.

## Data seed

`php artisan migrate:fresh --seed` menjalankan `UserSeeder`, `CategorySeeder`,
`ChartOfAccountSeeder`, dan `TransactionSeeder`:

- 5 kategori: Salary, Other Income (income); Family Expense, Transport Expense, Meal Expense (expense)
- 8 COA: 401 Gaji Karyawan, 402 Gaji Ketua MPR, 403 Profit Trading, 601 Biaya Sekolah,
  602 Bensin, 603 Parkir, 604 Makan Siang, 605 Makana Pokok Bulanan
- 3 transaksi: 2022-01-01 (credit 5.000.000), 2022-01-02 (credit 7.000.000),
  2022-01-10 (debit 25.000)

### Demo laporan (opsional)

`DemoReportSeeder` **tidak** termasuk `DatabaseSeeder`. Jalankan manual:

```bash
php artisan db:seed --class=DemoReportSeeder
```

Seeder ini mengganti transaksi Januari–Maret 2022 dengan data demo yang menghasilkan
Net Income persis seperti contoh laporan: Januari 16.650.000, Februari 13.950.000,
Maret 10.600.000. Aman dijalankan berulang (idempoten).

## Asumsi

- Semua user yang login berbagi data yang sama (tabel tidak punya `user_id`).
- Tanggal sumber pada soal berformat `M/D/YYYY` (`1/10/2022` = 10 Januari 2022); seeder
  memakai ISO `Y-m-d`.
- Angka contoh di soal tidak cocok dengan 3 transaksi sumber; `TransactionSeeder` memakai
  data persis dari soal, sedangkan `DemoReportSeeder` yang mereproduksi angka contoh.
- Registrasi publik dimatikan; user dibuat lewat seeder.
- Default rentang laporan: Januari–Desember tahun transaksi terbaru (tahun berjalan bila
  belum ada transaksi). Bila hanya salah satu filter diisi: `to` kosong → Desember tahun
  `from`; `from` kosong → Januari tahun `to`.

## Driver database

- Diuji: SQLite (file untuk dev, in-memory untuk test).
- MySQL/PostgreSQL belum dijalankan pada mesin ini. Kode tidak memakai SQL spesifik driver:
  tidak ada `DB::raw`/`selectRaw`, dan ekspresi periode dihitung di PHP.

## Hasil audit (2 November 2026)

- `composer audit` → **No security vulnerability advisories found**
- `npm audit` → **0 vulnerabilities**

## Keamanan

- Semua route aplikasi memakai middleware `auth`; hanya halaman login, forgot/reset password,
  dan landing page publik yang terbuka. CSV/Excel injection ditangani `App\Support\ExcelSanitizer`.
- Middleware `SecurityHeaders` mengirim `X-Content-Type-Options: nosniff`,
  `Referrer-Policy: strict-origin-when-cross-origin`, dan `X-Frame-Options: DENY`.
- Perubahan data hanya lewat POST/PUT/PATCH/DELETE dengan `@csrf`; tidak ada aksi ubah data via GET.
- Export dibatasi `throttle:10,1`; login memakai throttle bawaan Breeze.
- Input query divalidasi FormRequest; pencarian `LIKE` meng-escape `%` dan `_`.

## Catatan teknis

- Satu-satunya raw SQL di aplikasi: `whereRaw("code LIKE ? ESCAPE '!' ...")` pada
  `ChartOfAccountService` dan `whereRaw("description LIKE ? ESCAPE '!'")` pada
  `TransactionService`. SQL-nya konstan dan seluruh nilai masuk lewat binding; klausa
  `ESCAPE` diperlukan agar `%`/`_` bisa di-escape pada semua driver (SQLite tidak punya
  karakter escape default).
- Agregasi laporan mengambil baris pada rentang tanggal (memakai index `date`) lalu
  menjumlahkannya di PHP dengan BCMath, sehingga tidak ada `DB::raw`/`selectRaw`.
- `categories.name` di migrasi masih `varchar(50)` sementara validasi mengikuti spec
  (`max:100`). Perlebar kolom bila memakai MySQL/PostgreSQL.
