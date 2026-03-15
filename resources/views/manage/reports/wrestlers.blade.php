@extends('layouts.autowrestle')

@section('title', 'Wrestler Results – ' . $tournament->TournamentName)
@section('panel_title', 'Wrestler Results')

@section('content')
<x-page-header title="Search Wrestler Results – {{ $tournament->TournamentName }}">
    <x-slot:actions>
        <x-button href="{{ route('manage.reports.index', $tournament->id) }}" variant="ghost">Back to Reports</x-button>
    </x-slot:actions>
</x-page-header>

<x-card title="Search" class="mb-6">
    <form method="get" action="{{ route('manage.reports.wrestlers', $tournament->id) }}" class="flex flex-wrap items-end gap-4">
        <div class="min-w-[200px]">
            <label for="q" class="block text-sm font-medium text-slate-700">Wrestler name</label>
            <input type="text" name="q" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="First or last name" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
        </div>
        <div class="min-w-[160px]">
            <label for="team" class="block text-sm font-medium text-slate-700">Team / Club</label>
            <input type="text" name="team" id="team" value="{{ $filters['team'] ?? '' }}" placeholder="Filter by club" class="mt-1 block w-full rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <button type="submit" class="inline-flex items-center rounded-md bg-aw-primary px-3 py-2 text-sm font-medium text-white hover:bg-aw-primary/90">Search</button>
        </div>
    </form>
</x-card>

@if(isset($filters['q']) && $filters['q'] !== '')
    @if(empty($results))
        <x-card>
            <p class="text-slate-600">No wrestlers found matching "{{ e($filters['q']) }}".</p>
        </x-card>
    @else
        @foreach($results as $r)
            @php $w = $r['wrestler']; $history = $r['history']; @endphp
            <x-card class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-1">{{ $w->wr_first_name }} {{ $w->wr_last_name }}</h2>
                <p class="text-sm text-slate-600 mb-4">{{ $w->wr_club ? e($w->wr_club) : '—' }}</p>

                @if(empty($history))
                    <p class="text-slate-600">No completed brackets for this wrestler in this tournament.</p>
                @else
                    <p class="text-sm font-medium text-slate-700 mb-2">Placements in completed brackets:</p>
                    <ul class="space-y-2">
                        @foreach($history as $h)
                            <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                                <span class="font-medium text-slate-900">{{ $h['division_name'] }} {{ $h['group_name'] ? '— ' . $h['group_name'] : '' }}</span>
                                <span class="rounded bg-aw-primary/10 px-2 py-0.5 font-medium text-aw-primary">{{ ordinal($h['place']) }} place</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        @endforeach
    @endif
@else
    <x-card>
        <p class="text-slate-600">Enter a wrestler name (first or last) and click Search to see all completed brackets and placements for that wrestler in this tournament.</p>
    </x-card>
@endif
@endsection
