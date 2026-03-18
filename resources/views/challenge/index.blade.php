@extends('layouts.autowrestle')

@section('title', 'Challenge Match – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-3xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Challenge Match</h1>
        <p class="mb-4 text-slate-600">{{ $tournament->TournamentName }}</p>
        <p class="mb-6"><a href="{{ route('tournaments.show', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to tournament</a></p>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="mb-6">
            <x-button href="{{ route('challenge.create', $tournament->id) }}" variant="primary" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2.5 rounded-lg border-0 shadow-sm">
                Request challenge match
            </x-button>
        </div>

        <section class="mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-3">My wrestlers in this tournament</h2>
            @if($myTournamentWrestlers->isEmpty())
                <p class="text-slate-600">You have no wrestlers registered. <a href="{{ route('tournaments.register', $tournament->id) }}" class="text-aw-accent hover:underline">Register a wrestler</a> first.</p>
            @else
                <ul class="space-y-2">
                    @foreach($myTournamentWrestlers as $tw)
                        <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-900">
                            <span class="font-medium">{{ $tw->wr_first_name }} {{ $tw->wr_last_name }}</span>
                            <span class="text-sm text-slate-500">{{ $tw->wr_weight ?? '—' }} lbs · {{ $tw->wr_grade ?? '—' }} · {{ $tw->wr_club ?? '—' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-3">Incoming challenges</h2>
            @if($incoming->isEmpty())
                <p class="text-slate-600">No pending challenges for your wrestlers.</p>
            @else
                <ul class="space-y-3">
                    @foreach($incoming as $req)
                        <li class="rounded-lg border border-amber-200 bg-amber-50/50 px-4 py-3">
                            <p class="font-medium text-slate-900">Your wrestler <strong>{{ $req->challengedTournamentWrestler->wr_first_name }} {{ $req->challengedTournamentWrestler->wr_last_name }}</strong> has been challenged by <strong>{{ $req->challengerTournamentWrestler->wr_first_name }} {{ $req->challengerTournamentWrestler->wr_last_name }}</strong>.</p>
                            <p class="mt-1 text-sm text-slate-600">Sent {{ $req->created_at->diffForHumans() }}</p>
                            <div class="mt-3 flex gap-2">
                                <a href="{{ route('challenge.show', [$tournament->id, $req->id]) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">View & respond</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900 mb-3">Sent challenges</h2>
            @if($sent->isEmpty())
                <p class="text-slate-600">You have not sent any challenges yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($sent as $req)
                        <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2">
                            <span class="text-slate-900"><strong>{{ $req->challengerTournamentWrestler->wr_first_name }} {{ $req->challengerTournamentWrestler->wr_last_name }}</strong> → <strong>{{ $req->challengedTournamentWrestler->wr_first_name }} {{ $req->challengedTournamentWrestler->wr_last_name }}</strong></span>
                            <span class="text-sm font-medium
                                @if($req->status === 'pending_acceptance') text-amber-600
                                @elseif($req->status === 'accepted_pending_director') text-blue-600
                                @elseif($req->status === 'scheduled') text-green-600
                                @else text-slate-500
                                @endif">
                                {{ str_replace('_', ' ', ucfirst($req->status)) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </x-card>
</div>
@endsection
