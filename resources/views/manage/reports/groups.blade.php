@extends('layouts.autowrestle')

@section('title', 'Group Results – ' . $tournament->TournamentName)
@section('panel_title', 'Group Results')

@section('content')
<x-page-header title="View Results by Group – {{ $tournament->TournamentName }}">
    <x-slot:actions>
        <x-button href="{{ route('manage.reports.index', $tournament->id) }}" variant="ghost">Back to Reports</x-button>
    </x-slot:actions>
</x-page-header>

@if(empty($groupSummaries))
    <x-card>
        <p class="text-slate-600">No completed brackets in this tournament, or no groups with completed brackets.</p>
    </x-card>
@else
    <x-card title="Completed brackets by group">
        <p class="text-sm text-slate-600 mb-4">Click a group to see all completed brackets and placements in that group.</p>
        <ul class="space-y-4">
            @foreach($groupSummaries as $gs)
                <li class="border border-slate-200 rounded-lg p-4">
                    <h3 class="font-semibold text-slate-900 mb-2">{{ $gs['group_name'] }}</h3>
                    <p class="text-sm text-slate-600 mb-2">{{ $gs['bracket_count'] }} completed bracket(s)</p>
                    <x-button href="{{ route('manage.reports.groups.show', [$tournament->id, $gs['group_id']]) }}" variant="secondary">View group results</x-button>
                </li>
            @endforeach
        </ul>
    </x-card>
@endif
@endsection
