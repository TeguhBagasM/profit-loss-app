<div {{ $attributes->merge(['class' => 'w-full max-w-full overflow-x-auto border-t border-line']) }}>
    <table class="data-table min-w-[640px] border-collapse text-sm">
        {{ $slot }}
    </table>
</div>
