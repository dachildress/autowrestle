@extends('layouts.autowrestle')

@section('title', 'View – ' . $tournament->TournamentName)

@section('content')
<style>
.view-summary-boxes { display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-start; }
.view-summary-box { width: 240px; min-width: 240px; max-width: 240px; flex-shrink: 0; border: 1px solid #ecf0f1; border-radius: 4px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.view-summary-box-header { background: #3498db; color: #fff; padding: 10px 15px; font-weight: 700; font-size: 1.1rem; }
.view-summary-box-body { padding: 15px; background: #fff; }
.view-summary-row { padding: 4px 0; }
.view-summary-row span { margin-right: 0.5rem; }
</style>
<div style="text-align: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0;">{{ $tournament->TournamentName }} {{ $tournament->TournamentDate->format('m-d-Y') }}</h2>
</div>

<div class="view-summary-boxes">
    @foreach($divisionStats as $stat)
    <div class="view-summary-box">
        <div class="view-summary-box-header">{{ $stat->division->DivisionName }}</div>
        <div class="view-summary-box-body">
            <div class="view-summary-row"><span>Wrestlers:</span> <strong>{{ $stat->wrestlers }}</strong></div>
            <div class="view-summary-row"><span>Bouts:</span> <strong>{{ $stat->bouts }}</strong></div>
            <div class="view-summary-row"><span>Mats:</span> <strong>{{ $stat->mats }}</strong></div>
            <div class="view-summary-row"><span>Brackets:</span> <strong>{{ $stat->brackets }}</strong></div>
            <div class="view-summary-row"><span>Teams:</span> <strong>{{ $stat->teams }}</strong></div>
        </div>
    </div>
    @endforeach
</div>

@if(empty($divisionStats))
    <p>No divisions yet. <a href="{{ route('manage.divisions.index', $tournament->id) }}">Add divisions</a>.</p>
@endif
@endsection
