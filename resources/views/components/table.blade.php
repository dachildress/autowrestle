@props([
    'striped' => true,
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-lg border border-slate-200']) }}>
    <table class="min-w-full border-collapse">
        <thead>
            <tr class="bg-aw-primary text-white [&_th]:px-4 [&_th]:py-3 [&_th]:text-left [&_th]:text-sm [&_th]:font-semibold [&_th]:border-b [&_th]:border-slate-300">
                {{ $head }}
            </tr>
        </thead>
        <tbody @class(['divide-y divide-slate-200 [&_td]:px-4 [&_td]:py-3 [&_td]:text-sm', '[&>tr:nth-child(even)]:bg-slate-50' => $striped])>
            {{ $slot }}
        </tbody>
    </table>
</div>
