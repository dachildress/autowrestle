@extends('layouts.autowrestle')

@section('title', 'Challenge – ' . $challenge->challengerTournamentWrestler->wr_first_name . ' vs ' . $challenge->challengedTournamentWrestler->wr_first_name)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Incoming challenge</h1>
        <p class="mb-6"><a href="{{ route('challenge.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Challenge Match</a></p>

        <div class="rounded-lg border border-amber-200 bg-amber-50/50 px-4 py-3 mb-6">
            <p class="font-medium text-slate-900">Your wrestler <strong>{{ $challenge->challengedTournamentWrestler->wr_first_name }} {{ $challenge->challengedTournamentWrestler->wr_last_name }}</strong> has been challenged by <strong>{{ $challenge->challengerTournamentWrestler->wr_first_name }} {{ $challenge->challengerTournamentWrestler->wr_last_name }}</strong>.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 mb-6">
            <div class="rounded-xl border-2 border-slate-200 bg-slate-50/50 p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-2">Challenger</h3>
                <p class="font-semibold text-slate-900 text-lg">{{ $challenge->challengerTournamentWrestler->wr_first_name }} {{ $challenge->challengerTournamentWrestler->wr_last_name }}</p>
                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                    <li>Grade: {{ $challenge->challengerTournamentWrestler->wr_grade ?? '—' }}</li>
                    <li>Weight: {{ $challenge->challengerTournamentWrestler->wr_weight ?? '—' }} lbs</li>
                    <li>Experience: {{ $challenge->challengerTournamentWrestler->wr_years ?? '—' }} years</li>
                    <li>Club: {{ $challenge->challengerTournamentWrestler->wr_club ?? '—' }}</li>
                </ul>
            </div>
            <div class="rounded-xl border-2 border-aw-accent bg-white p-4">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-2">Your wrestler</h3>
                <p class="font-semibold text-slate-900 text-lg">{{ $challenge->challengedTournamentWrestler->wr_first_name }} {{ $challenge->challengedTournamentWrestler->wr_last_name }}</p>
                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                    <li>Grade: {{ $challenge->challengedTournamentWrestler->wr_grade ?? '—' }}</li>
                    <li>Weight: {{ $challenge->challengedTournamentWrestler->wr_weight ?? '—' }} lbs</li>
                    <li>Experience: {{ $challenge->challengedTournamentWrestler->wr_years ?? '—' }} years</li>
                    <li>Club: {{ $challenge->challengedTournamentWrestler->wr_club ?? '—' }}</li>
                </ul>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <form action="{{ route('challenge.accept', [$tournament->id, $challenge->id]) }}" method="post" class="inline">
                @csrf
                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-base font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Accept</button>
            </form>
            <form action="{{ route('challenge.decline', [$tournament->id, $challenge->id]) }}" method="post" class="inline">
                @csrf
                <button type="submit" class="rounded-lg bg-red-600 px-5 py-2.5 text-base font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Decline</button>
            </form>
            <a href="{{ route('challenge.index', $tournament->id) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-base font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </x-card>
</div>
@endsection
