@props([
    'label' => '',
    'value' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-lg shadow-sm p-4']) }}>
    @if(isset($value))
        <div class="text-2xl font-bold text-slate-900 tabular-nums">{{ $value }}</div>
    @else
        <div class="text-2xl font-bold text-slate-900 tabular-nums">{{ $slot }}</div>
    @endif
    @if($label)
        <div class="text-sm text-slate-500 mt-0.5">{{ $label }}</div>
    @endif
</div>
