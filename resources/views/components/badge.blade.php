@props([
    'variant' => 'default',
])

@php
$variants = [
    'default' => 'bg-slate-100 text-slate-700 border-slate-200',
    'primary' => 'bg-aw-primary text-white border-aw-primary',
    'accent' => 'bg-aw-accent text-white border-aw-accent',
    'success' => 'bg-green-100 text-green-800 border-green-200',
    'danger' => 'bg-red-100 text-red-800 border-red-200',
    'warning' => 'bg-amber-100 text-amber-800 border-amber-200',
];
$class = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</span>
