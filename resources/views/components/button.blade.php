@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'block' => false,
])

@php
$base = 'inline-flex items-center justify-center font-semibold rounded px-4 py-2 text-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-60 disabled:pointer-events-none';
$variants = [
    'primary' => 'bg-aw-primary text-white hover:bg-slate-800 focus:ring-aw-primary border border-aw-primary',
    'secondary' => 'bg-slate-600 text-white hover:bg-slate-500 focus:ring-slate-500 border border-slate-600',
    'accent' => 'bg-aw-accent text-white hover:bg-red-700 focus:ring-aw-accent border border-aw-accent',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-600 border border-red-600',
    'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-600 border border-green-600',
    'ghost' => 'bg-transparent text-slate-700 hover:bg-slate-100 focus:ring-slate-300 border border-transparent',
];
$class = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ($block ? ' w-full' : '');
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</a>
@else
<button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</button>
@endif
