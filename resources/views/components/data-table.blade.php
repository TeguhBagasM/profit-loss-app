<div {{ $attributes->merge(['class' => 'w-full overflow-x-auto border-t border-line']) }}>
    <table class="data-table min-w-full border-collapse text-sm">
        {{ $slot }}
    </table>
</div>
