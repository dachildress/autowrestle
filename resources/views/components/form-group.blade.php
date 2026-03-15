@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'inline' => false,
])

@php
$id = $name ? str_replace(['[', ']'], ['_', ''], $name) : null;
@endphp

<div @class([
    'mb-4' => true,
    'flex flex-wrap items-center gap-2' => $inline,
]) {{ $attributes->except('class') }}>
    @if($label)
        <label @if($id) for="{{ $id }}" @endif
               @class([
                   'block text-sm font-medium text-slate-700' => !$inline,
                   'w-36 shrink-0' => $inline,
               ])>
            {{ $label }}
            @if($required)<span class="text-red-600">*</span>@endif
        </label>
    @endif
    <div @class(['flex-1 min-w-0' => $inline])>
        {{ $slot }}
    </div>
    @if($hint)
        <p class="mt-1 text-sm text-slate-500">{{ $hint }}</p>
    @endif
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
