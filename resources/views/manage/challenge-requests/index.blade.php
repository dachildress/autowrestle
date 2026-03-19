@extends('layouts.autowrestle')

@section('title', 'Challenge requests – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-4xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Challenge requests</h1>
        <p class="mb-6 text-slate-600"><a href="{{ route('manage.tournaments.show', $tournament->id) }}" class="text-aw-accent hover:underline">← Match Board</a></p>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <section class="mb-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-3">Awaiting your approval</h2>
            @if($pending->isEmpty())
                <p class="text-slate-600">No challenge requests waiting for approval.</p>
            @else
                <ul class="space-y-3">
                    @foreach($pending as $req)
                        <li class="flex items-center justify-between rounded-lg border-2 border-amber-200 bg-amber-50/50 px-4 py-3">
                            <div>
                                <span class="font-semibold text-slate-900">{{ $req->challengerTournamentWrestler->wr_first_name }} {{ $req->challengerTournamentWrestler->wr_last_name }}</span>
                                <span class="text-slate-600"> vs </span>
                                <span class="font-semibold text-slate-900">{{ $req->challengedTournamentWrestler->wr_first_name }} {{ $req->challengedTournamentWrestler->wr_last_name }}</span>
                                <span class="ml-2 text-sm text-slate-500">Accepted {{ $req->accepted_at?->diffForHumans() }}</span>
                            </div>
                            <a href="{{ route('manage.challenge-requests.show', [$tournament->id, $req->id]) }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Review & approve</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section>
            <h2 class="text-lg font-semibold text-slate-900 mb-3">Other requests</h2>
            @if($other->isEmpty())
                <p class="text-slate-600">No other challenge requests.</p>
            @else
                <ul class="space-y-2">
                    @foreach($other as $req)
                        <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-2">
                            <span class="text-slate-900">{{ $req->challengerTournamentWrestler->wr_first_name }} {{ $req->challengerTournamentWrestler->wr_last_name }} vs {{ $req->challengedTournamentWrestler->wr_first_name }} {{ $req->challengedTournamentWrestler->wr_last_name }}</span>
                            <span class="text-sm font-medium
                                @if($req->status === 'scheduled') text-green-600
                                @elseif($req->status === 'declined_by_parent' || $req->status === 'declined_by_director') text-red-600
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
