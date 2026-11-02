@use('App\Support\Money')

@props(['value' => '0.00'])

@php
    $amount = (string) ($value ?? '0.00');
    $negative = Money::compare($amount, '0.00') < 0;
@endphp

<span {{ $attributes->merge([
    'class' => 'tabular-nums whitespace-nowrap text-right'.($negative ? ' text-loss' : ''),
]) }}>{{ Money::format($amount) }}</span>
