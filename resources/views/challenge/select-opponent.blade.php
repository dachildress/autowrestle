@extends('layouts.autowrestle')

@section('title', 'Challenge Match – Select opponent')

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Select opponent</h1>
        <p class="mb-2 text-slate-600">Challenger: <strong>{{ $challengerTw->wr_first_name }} {{ $challengerTw->wr_last_name }}</strong></p>
        <p class="mb-6"><a href="{{ route('challenge.create', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Change wrestler</a></p>

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <form action="{{ route('challenge.select-opponent', $tournament->id) }}" method="get" class="mb-6">
            <input type="hidden" name="challenger_tournament_wrestler_id" value="{{ $challengerTw->id }}">
            <div class="flex flex-wrap gap-3">
                <label class="flex-1 min-w-[180px]">
                    <span class="block text-sm font-medium text-slate-700 mb-1">Search by name</span>
                    <input type="text" name="q" value="{{ old('q', $search) }}" placeholder="First or last name"
                           class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-aw-accent focus:outline-none focus:ring-1 focus:ring-aw-accent">
                </label>
                <label class="min-w-[160px]">
                    <span class="block text-sm font-medium text-slate-700 mb-1">Division</span>
                    <select name="division_id" class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm focus:border-aw-accent focus:outline-none focus:ring-1 focus:ring-aw-accent">
                        <option value="">All divisions</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->id }}" {{ (string)$divisionId === (string)$div->id ? 'selected' : '' }}>{{ $div->DivisionName }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex items-end">
                    <button type="submit" class="rounded-lg border border-slate-300 bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-aw-accent focus:ring-offset-2">Search</button>
                </div>
            </div>
        </form>

        <h2 class="text-lg font-semibold text-slate-900 mb-3">Wrestlers in this tournament</h2>
        @if($opponents->isEmpty())
            <p class="text-slate-600">No other wrestlers found. Try adjusting the search or division filter.</p>
        @else
            <ul class="space-y-2">
                @foreach($opponents as $tw)
                    <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3">
                        <div>
                            <span class="font-medium text-slate-900">{{ $tw->wr_first_name }} {{ $tw->wr_last_name }}</span>
                            <span class="ml-2 text-sm text-slate-500">Weight {{ $tw->wr_weight ?? '—' }} · Grade {{ $tw->wr_grade ?? '—' }} · {{ $tw->wr_club ?? '—' }}</span>
                        </div>
                        <form action="{{ route('challenge.store', $tournament->id) }}" method="post" class="inline">
                            @csrf
                            <input type="hidden" name="challenger_tournament_wrestler_id" value="{{ $challengerTw->id }}">
                            <input type="hidden" name="challenged_tournament_wrestler_id" value="{{ $tw->id }}">
                            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Challenge</button>
                        </form>
                    </li>
                @endforeach
            </ul>
            @if($opponents->count() >= 100)
                <p class="mt-2 text-sm text-slate-500">Showing first 100 results. Narrow your search if needed.</p>
            @endif
        @endif

        <div class="mt-6 pt-4 border-t border-slate-200">
            <a href="{{ route('challenge.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Challenge Match</a>
        </div>
    </x-card>
</div>
@endsection
