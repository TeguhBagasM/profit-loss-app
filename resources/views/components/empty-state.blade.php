@props(['title', 'description' => null, 'actionLabel' => null, 'actionHref' => null, 'icon' => 'fa-solid fa-inbox'])

<div {{ $attributes->merge(['class' => 'px-4 py-12 text-center']) }}>
    <p class="text-base text-muted">
        <i class="{{ $icon }} text-xl" aria-hidden="true"></i>
    </p>

    <p class="mt-3 text-sm font-medium text-ink">{{ $title }}</p>

    @if ($description)
        <p class="mx-auto mt-1 max-w-sm text-sm text-muted">{{ $description }}</p>
    @endif

    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}"
           class="mt-4 inline-flex items-center gap-2 rounded-md bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
            <i class="fa-solid fa-plus w-3.5 text-center" aria-hidden="true"></i>
            {{ $actionLabel }}
        </a>
    @endif

    {{ $slot }}
</div>
