@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    @if(isset($breadcrumbs))
        <nav class="mb-2 text-sm text-slate-500" aria-label="Breadcrumb">
            {{ $breadcrumbs }}
        </nav>
    @endif
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            @if(isset($title))
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
            @else
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $slot }}</h1>
            @endif
            @if(isset($subtitle))
                <p class="mt-1 text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div class="shrink-0 flex items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
