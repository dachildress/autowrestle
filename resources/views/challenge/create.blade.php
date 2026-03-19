@extends('layouts.autowrestle')

@section('title', 'Challenge Match Request – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 mb-1">Challenge Match Request</h1>
        <p class="mb-6 text-slate-600">Select one of your wrestlers, then choose an opponent in your current tournament to challenge.</p>
        <p class="mb-6"><a href="{{ route('challenge.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Challenge Match</a></p>

        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <form action="{{ route('challenge.create', $tournament->id) }}" method="get" id="challenge-form" class="space-y-6">
            <div>
                <label for="challenger_tournament_wrestler_id" class="block text-sm font-medium text-slate-700 mb-2">Your Wrestler:</label>
                <select name="challenger_tournament_wrestler_id" id="challenger_tournament_wrestler_id"
                        class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 min-h-[44px]">
                    <option value="">Select wrestler</option>
                    @foreach($myTournamentWrestlers as $tw)
                        <option value="{{ $tw->id }}" {{ $challengerTw && $challengerTw->id === $tw->id ? 'selected' : '' }}>
                            {{ $tw->wr_first_name }} {{ $tw->wr_last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($challengerTw)
            <div>
                <label for="q" class="block text-sm font-medium text-slate-700 mb-2">Select Opponent to Challenge:</label>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-stretch">
                    <div class="relative min-w-0 flex-1">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="search" name="q" id="q" value="{{ old('q', $search) }}" placeholder="Search wrestlers..."
                               class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 min-h-[44px]">
                    </div>
                    <x-button type="submit" variant="primary" class="shrink-0 sm:min-w-[100px] justify-center">Search</x-button>
                </div>
            </div>
        </form>

            <div class="space-y-3">
                @if($opponents->isEmpty())
                    <p class="text-slate-600 py-4">No wrestlers in the same division found. Try a different search or select another wrestler.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($opponents as $tw)
                            <li class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-slate-200 text-slate-600 font-semibold text-lg">
                                    {{ strtoupper(mb_substr($tw->wr_first_name ?? '?', 0, 1)) }}{{ strtoupper(mb_substr($tw->wr_last_name ?? '?', 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-slate-900">{{ $tw->wr_first_name }} {{ $tw->wr_last_name }}</p>
                                    <p class="text-sm text-slate-600">Grade {{ $tw->wr_grade ?? '—' }} · {{ $tw->wr_weight ?? '—' }} lbs · {{ $tw->wr_years ?? '—' }} Years</p>
                                    @if($tw->wr_club)
                                        <p class="text-sm text-slate-500 mt-0.5">{{ $tw->wr_club }}</p>
                                    @endif
                                </div>
                                <form action="{{ route('challenge.store', $tournament->id) }}" method="post" class="challenge-submit-form shrink-0">
                                    @csrf
                                    <input type="hidden" name="challenger_tournament_wrestler_id" value="{{ $challengerTw->id }}">
                                    <input type="hidden" name="challenged_tournament_wrestler_id" value="{{ $tw->id }}">
                                    <x-button type="submit" variant="primary" class="rounded-lg">Challenge</x-button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    @if($opponents->count() >= 100)
                        <p class="text-sm text-slate-500">Showing first 100 results. Narrow your search if needed.</p>
                    @endif
                @endif
            </div>
            @else
            <p class="text-slate-500 py-2">Select your wrestler above to see opponents in the same division.</p>
            @endif

            <div class="flex justify-end pt-4">
                <a href="{{ route('challenge.index', $tournament->id) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">Cancel</a>
            </div>
    </x-card>
</div>

@if(session('success'))
<div id="challenge-success-toast" class="fixed bottom-6 left-1/2 z-50 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 animate-[toast-in_0.35s_ease-out]" role="status" aria-live="polite">
    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-white px-4 py-3 shadow-lg ring-1 ring-black/5">
        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </span>
        <div class="min-w-0 flex-1 pt-0.5">
            <p class="text-sm font-semibold text-slate-900">Challenge requested</p>
            <p class="mt-0.5 text-sm text-slate-600">{{ session('success') }}</p>
        </div>
        <button type="button" onclick="dismissChallengeToast()" class="shrink-0 rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Dismiss">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
<style>
@keyframes toast-in {
    from { opacity: 0; transform: translate(-50%, 12px); }
    to { opacity: 1; transform: translate(-50%, 0); }
}
</style>
@endif

<script>
function dismissChallengeToast() {
    var el = document.getElementById('challenge-success-toast');
    if (el) el.remove();
}
document.getElementById('challenger_tournament_wrestler_id')?.addEventListener('change', function() {
    this.form.submit();
});
document.getElementById('q')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') this.form.submit();
});
@if(session('success'))
(function() {
    setTimeout(dismissChallengeToast, 5000);
})();
@endif
</script>
@endsection
