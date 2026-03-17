@extends('layouts.autowrestle')

@section('title', 'Check-in – ' . $group->Name . ' – ' . $tournament->TournamentName)

@section('content')
<h1>Check-in: {{ $division->DivisionName }} – {{ $group->Name }}</h1>
<p><a href="{{ route('manage.checkin.index', $tournament->id) }}">← All groups</a> | <a href="{{ route('manage.tournaments.show', $tournament->id) }}">Tournament</a></p>

<ul class="group-tabs">
    @foreach($groups as $g)
        <li class="{{ $g->division_id == $selected_did && $g->id == $selected_gid ? 'active' : '' }}">
            @if($g->division_id == $selected_did && $g->id == $selected_gid)
                <strong>{{ $g->division_name }} – {{ $g->Name }}</strong>
            @else
                <a href="{{ route('manage.checkin.show', [$tournament->id, $g->division_id, $g->id]) }}">{{ $g->division_name }} – {{ $g->Name }}</a>
            @endif
        </li>
    @endforeach
</ul>

@if(session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

<p>
    <label><input type="checkbox" id="checkall" onclick="toggleAll(this)"> Check all</label>
</p>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Club</th>
            <th style="text-align: center;">Age</th>
            <th style="text-align: center;">Grade</th>
            <th style="text-align: center;">Weight</th>
            <th style="text-align: center;">Years</th>
            <th>Check-in</th>
        </tr>
    </thead>
    <tbody>
        @foreach($wrestlers as $w)
        <tr>
            <td>{{ $w->wr_first_name }} {{ $w->wr_last_name }}</td>
            <td>{{ $w->wr_club }}</td>
            <td style="text-align: center;">{{ $w->wr_age }}</td>
            <td style="text-align: center;">{{ $w->wr_grade }}</td>
            <td style="text-align: center;">{{ $w->wr_weight }}</td>
            <td style="text-align: center;">{{ $w->wr_years }}</td>
            <td>
                <input type="checkbox" class="check-one" data-wid="{{ $w->id }}" data-checked="{{ $w->checked_in ? '1' : '0' }}"
                    {{ $w->checked_in ? 'checked' : '' }} onchange="updateCheckin({{ $w->id }}, this.checked ? 1 : 0)">
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@if($wrestlers->isEmpty())
    <p>No wrestlers in this group.</p>
@endif

<script>
var checkinUpdateBase = "{{ route('manage.checkin.update', [$tournament->id, '__ID__', '__VAL__']) }}";
function updateCheckin(wid, value) {
    var url = checkinUpdateBase.replace('__ID__', wid).replace('__VAL__', value);
    fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(function() { window.location.reload(); });
}
function toggleAll(checkbox) {
    var val = checkbox.checked ? 1 : 0;
    var url = checkinUpdateBase.replace('__ID__', 'all').replace('__VAL__', val);
    fetch(url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(function() { window.location.reload(); });
}
</script>
@endsection
