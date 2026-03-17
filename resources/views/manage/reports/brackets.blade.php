@extends('layouts.autowrestle')

@section('title', 'Bracket Results – ' . $tournament->TournamentName)
@section('panel_title', 'Bracket Results')

@section('content')
<x-page-header title="View Results by Bracket – {{ $tournament->TournamentName }}">
    <x-slot:actions>
        <x-button href="{{ route('manage.reports.index', $tournament->id) }}" variant="ghost">Back to Reports</x-button>
    </x-slot:actions>
</x-page-header>

<x-card title="Filter by bracket ID" class="mb-6">
    <form method="get" action="{{ route('manage.reports.brackets', $tournament->id) }}" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="bracket_id" class="block text-sm font-medium text-slate-700">Bracket ID</label>
            <input type="number" name="bracket_id" id="bracket_id" value="{{ $filters['bracket_id'] ?? '' }}" placeholder="e.g. 5" min="1" class="mt-1 block w-24 rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center rounded-md bg-aw-primary px-3 py-2 text-sm font-medium text-white hover:bg-aw-primary/90">Show</button>
        </div>
    </form>
</x-card>

<x-card title="Completed brackets" :padding="false">
    @if($summaries->isEmpty())
        <div class="p-6 text-slate-600">No completed brackets in this tournament.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Division / Group</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Bracket</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Champion</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($summaries as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-700">{{ $s['division_name'] }} — {{ $s['group_name'] }}</td>
                        <td class="px-4 py-3">#{{ $s['bracket_id'] }} ({{ $s['wrestler_count'] }})</td>
                        <td class="px-4 py-3 text-slate-900">{{ $s['champion'] ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-button href="{{ route('manage.reports.brackets.show', [$tournament->id, $s['bracket_id']]) }}" variant="primary">View results</x-button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
@endsection
