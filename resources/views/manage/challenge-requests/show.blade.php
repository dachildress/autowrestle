@extends('layouts.autowrestle')

@section('title', 'Challenge request – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-3xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Review challenge request</h1>
        <p class="mb-6"><a href="{{ route('manage.challenge-requests.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Challenge requests</a></p>

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="grid gap-6 sm:grid-cols-2 mb-6">
            @php
                $c = $challenge->challengerTournamentWrestler;
                $d = $challenge->challengedTournamentWrestler;
                $weightMismatch = ($c->wr_weight && $d->wr_weight && abs((float)$c->wr_weight - (float)$d->wr_weight) > 5);
                $gradeMismatch = ($c->wr_grade && $d->wr_grade && $c->wr_grade !== $d->wr_grade);
            @endphp
            <div class="rounded-xl border-2 border-slate-200 bg-slate-50/50 p-4 {{ $weightMismatch || $gradeMismatch ? 'ring-2 ring-amber-400' : '' }}">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-2">Challenger</h3>
                <p class="font-semibold text-slate-900 text-lg">{{ $c->wr_first_name }} {{ $c->wr_last_name }}</p>
                @if($challenge->challengerUser)
                    <p class="text-sm text-slate-500 mt-1">Parent: {{ $challenge->challengerUser->name ?? $challenge->challengerUser->email }}</p>
                @endif
                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                    <li>Grade: {{ $c->wr_grade ?? '—' }}</li>
                    <li>Weight: {{ $c->wr_weight ?? '—' }} lbs</li>
                    <li>Experience: {{ $c->wr_years ?? '—' }} years</li>
                    <li>Club: {{ $c->wr_club ?? '—' }}</li>
                </ul>
            </div>
            <div class="rounded-xl border-2 border-slate-200 bg-slate-50/50 p-4 {{ $weightMismatch || $gradeMismatch ? 'ring-2 ring-amber-400' : '' }}">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-2">Challenged</h3>
                <p class="font-semibold text-slate-900 text-lg">{{ $d->wr_first_name }} {{ $d->wr_last_name }}</p>
                @if($challenge->challengedUser)
                    <p class="text-sm text-slate-500 mt-1">Parent: {{ $challenge->challengedUser->name ?? $challenge->challengedUser->email }}</p>
                @endif
                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                    <li>Grade: {{ $d->wr_grade ?? '—' }}</li>
                    <li>Weight: {{ $d->wr_weight ?? '—' }} lbs</li>
                    <li>Experience: {{ $d->wr_years ?? '—' }} years</li>
                    <li>Club: {{ $d->wr_club ?? '—' }}</li>
                </ul>
            </div>
        </div>

        @if($weightMismatch || $gradeMismatch)
            <div class="mb-6 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                <strong>Note:</strong>
                @if($weightMismatch) Weight difference may be significant.
                @endif
                @if($gradeMismatch) Different grades.
                @endif
                These are not hard blocks; approve only if appropriate.
            </div>
        @endif

        @if($challenge->status === \App\Models\ChallengeRequest::STATUS_ACCEPTED_PENDING_DIRECTOR)
            <div class="flex flex-wrap gap-4">
                <form action="{{ route('manage.challenge-requests.approve', [$tournament->id, $challenge->id]) }}" method="post" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 mb-1">Assign to mat</span>
                        <select name="mat_number" required class="block w-full min-w-[100px] rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-aw-accent focus:outline-none focus:ring-1 focus:ring-aw-accent">
                            @foreach($mats as $m)
                                <option value="{{ $m }}">Mat {{ $m }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="rounded-lg bg-green-600 px-5 py-2.5 text-base font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Approve & schedule</button>
                </form>
                <form action="{{ route('manage.challenge-requests.decline', [$tournament->id, $challenge->id]) }}" method="post" class="inline">
                    @csrf
                    <label class="block mb-2">
                        <span class="block text-sm font-medium text-slate-700 mb-1">Notes (optional)</span>
                        <input type="text" name="director_notes" placeholder="Reason for decline" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-aw-accent focus:outline-none focus:ring-1 focus:ring-aw-accent">
                    </label>
                    <button type="submit" class="rounded-lg bg-red-600 px-5 py-2.5 text-base font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Decline</button>
                </form>
            </div>
        @else
            <p class="text-slate-600">Status: <strong>{{ str_replace('_', ' ', ucfirst($challenge->status)) }}</strong>. No action available.</p>
        @endif
    </x-card>
</div>
@endsection
