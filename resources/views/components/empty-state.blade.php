@props(['title', 'description' => null, 'actionLabel' => null, 'actionHref' => null])

<div {{ $attributes->merge(['class' => 'px-4 py-12 text-center']) }}>
    <p class="text-sm font-medium text-ink">{{ $title }}</p>

    @if ($description)
        <p class="mx-auto mt-1 max-w-sm text-sm text-muted">{{ $description }}</p>
    @endif

    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}"
           class="mt-4 inline-flex items-center rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
            {{ $actionLabel }}
        </a>
    @endif

    {{ $slot }}
</div>
