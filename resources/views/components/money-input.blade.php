@props(['name', 'id' => null, 'value' => ''])

@php
    $inputId = $id ?? $name;
@endphp

<div x-data="moneyInput({{ Js::from(['value' => (string) $value]) }})">
    <input
        x-ref="visible"
        id="{{ $inputId }}"
        type="text"
        inputmode="decimal"
        autocomplete="off"
        x-on:input="reformat($event.target)"
        x-on:paste="onPaste($event)"
        {{ $attributes->merge(['class' => 'input-field']) }}
    >

    <input x-ref="hidden" type="hidden" name="{{ $name }}" value="{{ $value }}">
</div>
