@extends('layouts.autowrestle')

@section('title', 'Bracket #' . $bracket_id . ' – ' . ($meta['tournament_name'] ?? 'Report'))
@section('panel_title', 'Bracket Results')

@section('content')
<x-page-header :title="'Bracket #' . $bracket_id . ' — ' . ($meta['division_name'] ?? '')">
    <x-slot:actions>
        <button type="button" onclick="window.print();" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 no-print">Print</button>
        <x-button href="{{ route('manage.reports.brackets', $tournament->id) }}" variant="ghost" class="no-print">Back to brackets</x-button>
    </x-slot:actions>
</x-page-header>

<x-card class="mb-6 print:shadow-none">
    <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
        <dt class="font-medium text-slate-500">Tournament</dt>
        <dd class="text-slate-900">{{ $meta['tournament_name'] ?? '—' }}</dd>
        <dt class="font-medium text-slate-500">Date</dt>
        <dd class="text-slate-900">{{ $meta['tournament_date'] ? $meta['tournament_date']->format('M j, Y') : '—' }}</dd>
        <dt class="font-medium text-slate-500">Division</dt>
        <dd class="text-slate-900">{{ $meta['division_name'] ?? '—' }}</dd>
        <dt class="font-medium text-slate-500">Group</dt>
        <dd class="text-slate-900">{{ $meta['group_name'] ?? '—' }}</dd>
        <dt class="font-medium text-slate-500">Bracket</dt>
        <dd class="text-slate-900">#{{ $bracket_id }} ({{ $meta['wrestler_count'] ?? 0 }} wrestlers)</dd>
        <dt class="font-medium text-slate-500">Completed</dt>
        <dd class="text-slate-900">{{ $completed_at ? ($completed_at instanceof \DateTimeInterface ? $completed_at->format('M j, Y g:i A') : $completed_at) : '—' }}</dd>
    </dl>
</x-card>

<x-card title="Final placements" :padding="false">
    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse reports-table">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-100">
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Place</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Name</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Club</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Wins</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($placements as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $p['place'] }}</td>
                        <td class="px-4 py-3 text-slate-900">{{ $p['name'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $p['club'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $p['wins'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-card>

@push('styles')
<style>
@media print {
    .no-print, .nav-bar { display: none !important; }
    .print\:shadow-none { box-shadow: none; }
}
</style>
@endpush
@endsection
