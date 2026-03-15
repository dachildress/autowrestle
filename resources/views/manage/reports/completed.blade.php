@extends('layouts.autowrestle')

@section('title', 'Completed Brackets – ' . $tournament->TournamentName)
@section('panel_title', 'Completed Brackets')

@section('content')
<x-page-header title="Completed Brackets – {{ $tournament->TournamentName }}">
    <x-slot:actions>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Export CSV</a>
        <button type="button" onclick="window.print();" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Print</button>
        <x-button href="{{ route('manage.reports.index', $tournament->id) }}" variant="ghost">Back to Reports</x-button>
    </x-slot:actions>
</x-page-header>

<x-card title="Filters" class="mb-6 print:shadow-none">
    <form method="get" action="{{ route('manage.reports.completed', $tournament->id) }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="date_from" class="block text-sm font-medium text-slate-700">Date from</label>
            <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 block rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <label for="date_to" class="block text-sm font-medium text-slate-700">Date to</label>
            <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 block rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center rounded-md bg-aw-primary px-3 py-2 text-sm font-medium text-white hover:bg-aw-primary/90">Apply</button>
        </div>
    </form>
</x-card>

<x-card title="Results" :padding="false" class="print:shadow-none">
    @if($summaries->isEmpty())
        <div class="p-6 text-slate-600">No completed brackets in this tournament.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse reports-table">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Division</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Group</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Bracket</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Status</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Completed</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Champion</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-900 no-print">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($summaries as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-700">{{ $s['division_name'] }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $s['group_name'] }}</td>
                        <td class="px-4 py-3 text-slate-700">#{{ $s['bracket_id'] }} ({{ $s['wrestler_count'] }} wrestlers)</td>
                        <td class="px-4 py-3"><span class="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Completed</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ $s['completed_at'] ? ($s['completed_at'] instanceof \DateTimeInterface ? $s['completed_at']->format('M j, Y g:i A') : $s['completed_at']) : '—' }}</td>
                        <td class="px-4 py-3 text-slate-900">{{ $s['champion'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right no-print">
                            <x-button href="{{ route('manage.reports.brackets.show', [$tournament->id, $s['bracket_id']]) }}" variant="ghost" class="!py-1 !text-sm">View</x-button>
                            <a href="{{ route('manage.reports.brackets.show', [$tournament->id, $s['bracket_id']]) }}?print=1" target="_blank" class="text-sm text-aw-accent hover:underline">Print</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
@endsection

@push('styles')
<style>
@media print {
    .no-print, .nav-bar, .main .container > .success, .main .container > .error { display: none !important; }
    .print\:shadow-none { box-shadow: none; }
}
</style>
@endpush
