<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-paper font-sans text-ink antialiased">
        @php
            $navigation = [
                ['label' => 'Dashboard', 'icon' => 'fa-solid fa-gauge-high', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
                ['label' => 'Transaksi', 'icon' => 'fa-solid fa-receipt', 'href' => route('transactions.index'), 'active' => request()->routeIs('transactions.*')],
                ['label' => 'COA', 'icon' => 'fa-solid fa-list', 'href' => route('chart-of-accounts.index'), 'active' => request()->routeIs('chart-of-accounts.*')],
                ['label' => 'Kategori', 'icon' => 'fa-solid fa-tags', 'href' => route('categories.index'), 'active' => request()->routeIs('categories.*')],
                ['label' => 'Laporan', 'icon' => 'fa-solid fa-chart-line', 'href' => route('reports.profit-loss'), 'active' => request()->routeIs('reports.*')],
            ];
        @endphp

        <div class="md:flex md:min-h-screen">
            <aside class="hidden md:flex md:w-56 md:shrink-0 md:flex-col md:border-r md:border-line md:bg-surface">
                <div class="px-5 py-6">
                    <a href="{{ route('dashboard') }}" class="text-base font-semibold tracking-tight text-ink">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <nav class="flex-1 space-y-0.5 px-2" aria-label="Navigasi utama">
                    @foreach ($navigation as $item)
                        <a href="{{ $item['href'] }}"
                           @class([
                               'block rounded-md px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-accent',
                               'bg-paper font-medium text-ink' => $item['active'],
                               'text-muted hover:text-ink' => ! $item['active'],
                           ])
                           @if ($item['active']) aria-current="page" @endif>
                            <i class="{{ $item['icon'] }} w-4 text-center" aria-hidden="true"></i>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-line px-5 py-4">
                    <p class="truncate text-sm text-muted">{{ Auth::user()->email }}</p>

                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="text-sm text-muted underline hover:text-ink">
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="border-b border-line bg-surface md:hidden" x-data="{ open: false }">
                    <div class="flex items-center justify-between px-4 py-3">
                        <a href="{{ route('dashboard') }}" class="text-base font-semibold tracking-tight text-ink">
                            {{ config('app.name', 'Laravel') }}
                        </a>

                        <button type="button" x-on:click="open = ! open" :aria-expanded="open.toString()"
                                aria-controls="navigasi-mobile" aria-label="Buka menu"
                                class="rounded-md border border-line p-2 text-muted hover:text-ink">
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>
                    </div>

                    <nav id="navigasi-mobile" x-show="open" x-cloak class="border-t border-line px-4 py-3"
                         aria-label="Navigasi utama">
                        <div class="space-y-0.5">
                            @foreach ($navigation as $item)
                                <a href="{{ $item['href'] }}"
                                   @class([
                                       'block rounded-md px-3 py-2 text-sm',
                                       'bg-paper font-medium text-ink' => $item['active'],
                                       'text-muted hover:text-ink' => ! $item['active'],
                                   ])
                                   @if ($item['active']) aria-current="page" @endif>
                                    <i class="{{ $item['icon'] }} w-4 text-center" aria-hidden="true"></i>
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-3 border-t border-line pt-3">
                            <p class="truncate text-sm text-muted">{{ Auth::user()->email }}</p>

                            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="text-sm text-muted underline hover:text-ink">Keluar</button>
                            </form>
                        </div>
                    </nav>
                </header>

                <main class="px-4 py-6 sm:px-6 sm:py-8 lg:px-10">
                    <div class="mx-auto w-full max-w-[1100px]">
                        @isset($header)
                            <div class="mb-6">{{ $header }}</div>
                        @endisset

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
