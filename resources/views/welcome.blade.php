<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        <div class="mx-auto flex min-h-screen max-w-[1100px] flex-col justify-center px-4 py-12 sm:px-6 lg:px-8">
            <h1 class="max-w-xl text-2xl font-semibold tracking-tight">
                Laporan profit/loss tanpa spreadsheet terpisah
            </h1>

            <p class="mt-3 max-w-xl text-sm text-muted">
                Catat transaksi, kelompokkan per kategori dan chart of account, lalu baca laba
                per bulan. Export ke Excel kalau perlu dibagikan.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                        Buka dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                        Masuk
                    </a>
                @endauth
            </div>

            <p class="mt-10 text-sm text-muted">
                Akun dibuat lewat seeder, bukan registrasi publik.
            </p>
        </div>
    </body>
</html>
