@props([
    'title' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden']) }}>
    @if($title || isset($header))
        <div class="bg-slate-100 border-b border-slate-200 px-4 py-3">
            @if(isset($header))
                {{ $header }}
            @else
                <h2 class="text-base font-semibold text-slate-900 m-0">{{ $title }}</h2>
            @endif
        </div>
    @endif
    <div @class(['p-4' => $padding])>
        {{ $slot }}
    </div>
</div>
