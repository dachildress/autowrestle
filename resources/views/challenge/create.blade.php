@extends('layouts.autowrestle')

@section('title', 'Challenge Match – Select your wrestler')

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Request challenge match</h1>
        <p class="mb-2 text-slate-600">Step 1: Select which of your wrestlers will issue the challenge.</p>
        <p class="mb-6"><a href="{{ route('challenge.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Challenge Match</a></p>

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <form action="{{ route('challenge.select-opponent', $tournament->id) }}" method="get" class="space-y-4">
            <label class="block text-sm font-medium text-slate-700">My wrestler (challenger)</label>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($myTournamentWrestlers as $tw)
                    <label class="relative flex cursor-pointer rounded-xl border-2 border-slate-200 bg-white p-4 shadow-sm hover:border-aw-accent hover:bg-slate-50/50 has-[:checked]:border-aw-accent has-[:checked]:ring-2 has-[:checked]:ring-aw-accent/20">
                        <input type="radio" name="challenger_tournament_wrestler_id" value="{{ $tw->id }}" class="sr-only peer" required>
                        <span class="block">
                            <span class="font-semibold text-slate-900">{{ $tw->wr_first_name }} {{ $tw->wr_last_name }}</span>
                            <span class="mt-1 block text-sm text-slate-500">Weight {{ $tw->wr_weight ?? '—' }} · Grade {{ $tw->wr_grade ?? '—' }} · {{ $tw->wr_club ?? '—' }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Continue to select opponent</button>
                <a href="{{ route('challenge.index', $tournament->id) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </x-card>
</div>
@endsection
