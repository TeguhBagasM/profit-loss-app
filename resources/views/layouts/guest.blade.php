<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="/" class="text-base font-semibold tracking-tight text-ink">
                {{ config('app.name', 'Laravel') }}
            </a>

            <div class="mt-6 w-full max-w-sm border border-line bg-surface p-6">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
