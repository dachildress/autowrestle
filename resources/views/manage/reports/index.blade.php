@extends('layouts.autowrestle')

@section('title', 'Reports – ' . $tournament->TournamentName)
@section('panel_title', 'Reports')

@section('content')
<x-page-header title="Reports – {{ $tournament->TournamentName }}">
    <x-slot:actions>
        <x-button href="{{ route('manage.reports.completed', $tournament->id) }}" variant="primary">Completed Brackets</x-button>
    </x-slot:actions>
</x-page-header>

<p class="mb-6 text-slate-600">View completed bracket results, group and bracket reports, and wrestler placement history for this tournament.</p>

<div class="grid gap-6 md:grid-cols-2">
    <x-card title="Completed Brackets">
        <p class="text-sm text-slate-600 mb-4">List all finished brackets with division, group, champion, and completion date.</p>
        <x-button href="{{ route('manage.reports.completed', $tournament->id) }}" variant="secondary">View completed brackets</x-button>
    </x-card>

    <x-card title="Group Results">
        <p class="text-sm text-slate-600 mb-4">See all completed brackets in each group.</p>
        <x-button href="{{ route('manage.reports.groups', $tournament->id) }}" variant="secondary">View by group</x-button>
    </x-card>

    <x-card title="Bracket Results">
        <p class="text-sm text-slate-600 mb-4">Search or filter by bracket and open a bracket report with final placements.</p>
        <x-button href="{{ route('manage.reports.brackets', $tournament->id) }}" variant="secondary">View bracket results</x-button>
    </x-card>

    <x-card title="Wrestler Results">
        <p class="text-sm text-slate-600 mb-4">Search by wrestler name or team to see all completed brackets and placements for that wrestler.</p>
        <x-button href="{{ route('manage.reports.wrestlers', $tournament->id) }}" variant="secondary">Search wrestler results</x-button>
    </x-card>
</div>
@endsection
