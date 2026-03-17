@extends('layouts.autowrestle')

@section('title', 'Group Results – ' . $group->Name . ' – ' . $tournament->TournamentName)
@section('panel_title', 'Group: ' . $group->Name)

@section('content')
<x-page-header :title="'Group: ' . $group->Name">
    <x-slot:actions>
        <button type="button" onclick="window.print();" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Print</button>
        <x-button href="{{ route('manage.reports.groups', $tournament->id) }}" variant="ghost">Back to groups</x-button>
    </x-slot:actions>
</x-page-header>

<p class="mb-4 text-slate-600">{{ $tournament->TournamentName }} — {{ $tournament->TournamentDate->format('M j, Y') }}</p>

@if($summaries->isEmpty())
    <x-card>
        <p class="text-slate-600">No completed brackets in this group.</p>
    </x-card>
@else
    @foreach($summaries as $s)
        <x-card class="mb-6 print:shadow-none" :title="'Bracket #' . $s['bracket_id'] . ' — ' . $s['division_name']">
            <p class="text-sm text-slate-600 mb-3">Completed {{ $s['completed_at'] ? ($s['completed_at'] instanceof \DateTimeInterface ? $s['completed_at']->format('M j, Y g:i A') : $s['completed_at']) : '—' }}</p>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-100">
                            <th class="px-3 py-2 text-left font-semibold text-slate-900">Place</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-900">Name</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-900">Club</th>
                            <th class="px-3 py-2 text-left font-semibold text-slate-900">Wins</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($s['placements'] as $p)
                            <tr class="border-b border-slate-100">
                                <td class="px-3 py-2">{{ $p['place'] }}</td>
                                <td class="px-3 py-2">{{ $p['name'] }}</td>
                                <td class="px-3 py-2">{{ $p['club'] }}</td>
                                <td class="px-3 py-2">{{ $p['wins'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 no-print">
                <x-button href="{{ route('manage.reports.brackets.show', [$tournament->id, $s['bracket_id']]) }}" variant="ghost" class="!py-1 no-print">View bracket detail</x-button>
            </p>
        </x-card>
    @endforeach
@endif

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .nav-bar { display: none !important; }
}
</style>
@endpush
@endsection
