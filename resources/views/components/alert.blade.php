@props([
    'variant' => 'info',
    'dismissible' => false,
])

@php
$variants = [
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    'info' => 'bg-sky-50 border-sky-200 text-sky-800',
];
$class = 'rounded-lg border px-4 py-3 ' . ($variants[$variant] ?? $variants['info']);
@endphp

<div {{ $attributes->merge(['class' => $class]) }} role="alert">
    <div class="flex items-start gap-3">
        <div class="flex-1">{{ $slot }}</div>
        @if($dismissible)
            <button type="button" onclick="this.closest('[role=alert]').style.display='none'" class="shrink-0 rounded p-1 hover:bg-black/5 focus:outline-none focus:ring-2 focus:ring-slate-300" aria-label="Dismiss">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </button>
        @endif
    </div>
</div>
