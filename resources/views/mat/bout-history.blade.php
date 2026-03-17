@extends('layouts.mat')

@section('title', 'Summary – Bout ' . $boutId)
@section('panel_title', 'Summary – Bout ' . $boutId)

@section('content')
@include('mat.nav', ['boutId' => $boutId, 'current' => 'history'])

<p><strong>{{ $divisionName }}</strong> — Bout {{ $boutId }}</p>
<div style="margin: 1rem 0;">
    <span style="color: #c00;">{{ $redWrestler->wr_first_name }} {{ $redWrestler->wr_last_name }} ({{ $redWrestler->wr_club }})</span>
    <span style="margin: 0 0.5rem;">vs</span>
    <span style="color: #080;">{{ $greenWrestler->wr_first_name }} {{ $greenWrestler->wr_last_name }} ({{ $greenWrestler->wr_club }})</span>
</div>

@if($state)
    <p><strong>Score:</strong> Red {{ $state->red_score }} – Green {{ $state->green_score }}@if($state->isCompleted()) (Final)@endif</p>
@endif

<h3>Event log by period</h3>
@forelse($eventsByPeriod as $period => $periodEvents)
    <div style="margin-bottom: 1.5rem;">
        <h4>{{ $period == 0 ? 'General' : 'Period ' . $period }}</h4>
        <ul style="list-style: none; padding: 0;">
            @foreach($periodEvents as $e)
                <li style="padding: 0.25rem 0; border-bottom: 1px solid #eee;">
                    @if($e->side === 'red')<span style="color: #c00;">Red</span>@elseif($e->side === 'green')<span style="color: #080;">Green</span>@else<span>—</span>@endif
                    {{ $e->event_type }}@if($e->points) ({{ $e->points }})@endif
                    @if($e->match_time_snapshot !== null)
                        <span style="color: #666;">({{ (int)($e->match_time_snapshot / 60) }}:{{ sprintf('%02d', $e->match_time_snapshot % 60) }})</span>
                    @endif
                    @if($e->note)<br><em>{{ $e->note }}</em>@endif
                </li>
            @endforeach
        </ul>
    </div>
@empty
    <p>No events recorded yet.</p>
@endforelse

<p><a href="{{ route('mat.bout.show', ['boutId' => $boutId]) }}" class="btn btn-primary">← Back to scoring</a></p>
@endsection
