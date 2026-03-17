@extends('layouts.autowrestle')

@section('title', 'Period settings – ' . $division->DivisionName)

@section('content')
<div style="text-align: center; margin-bottom: 1rem;">
    <h2 style="margin: 0;">{{ $tournament->TournamentName }} {{ $tournament->TournamentDate?->format('m-d-Y') }}</h2>
</div>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if(session('error'))<p class="error">{{ session('error') }}</p>@endif
<p>
    <a href="{{ route('manage.divisions.show', [$tournament->id, $division->id]) }}">&larr; Back to {{ $division->DivisionName }}</a>
    &nbsp;|&nbsp;
    <a href="{{ route('manage.divisions.index', $tournament->id) }}">Divisions</a>
</p>

<h2>Period timing: {{ $division->DivisionName }}</h2>
<p>Configure match period durations for this division. Mat-side scoring will use these values.</p>

<form method="post" action="{{ route('manage.divisions.period-settings.store', [$tournament->id, $division->id]) }}" id="period-form">
    @csrf
    <table class="table-dark">
        <thead>
            <tr>
                <th>Period</th>
                <th>Duration (mm:ss)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periods as $p)
            <tr>
                <td>{{ $p['period_label'] }}</td>
                <td>
                    @php
                        $sec = (int) ($p['duration_seconds'] ?? 0);
                        $min = (int) floor($sec / 60);
                        $s = $sec % 60;
                    @endphp
                    <input type="number" name="minutes_{{ $p['period_code'] }}" value="{{ old('minutes_' . $p['period_code'], $min) }}" min="0" max="120" style="width:4em;"> :
                    <input type="number" name="seconds_{{ $p['period_code'] }}" value="{{ old('seconds_' . $p['period_code'], $s) }}" min="0" max="59" style="width:4em;">
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p>
        <button type="submit" class="btn">Save period settings</button>
    </p>
</form>

<form method="post" action="{{ route('manage.divisions.period-settings.defaults', [$tournament->id, $division->id]) }}" style="display:inline;" onsubmit="return confirm('Add default period rows for any missing periods?');">
    @csrf
    <button type="submit" class="btn">Initialize defaults</button>
</form>
<p class="muted">Use &quot;Initialize defaults&quot; if this division has no period rows yet. Existing rows are not overwritten.</p>

@if($errors->any())
    <ul class="error-list">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
@endif
@endsection
