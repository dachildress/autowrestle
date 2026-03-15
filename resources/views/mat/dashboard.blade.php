@extends('layouts.mat')

@section('title', 'Mat dashboard')
@section('panel_title', 'Mat dashboard')

@section('content')
@if($matNumber !== null)
@include('mat.nav')
@endif
@if($matNumber === null)
    <p class="error">You have no mat assigned. Contact an administrator to assign your mat number.</p>
@else
    <p>You are assigned to <strong>Mat {{ $matNumber }}</strong>@if($tournament) for <strong>{{ $tournament->TournamentName }}</strong>@endif.</p>

    @if(!$tournament)
        <p class="error">No tournament assigned. Contact an administrator to set your tournament.</p>
    @else
        <form method="get" action="{{ route('mat.dashboard') }}" class="form-horizontal" style="margin-bottom: 1.5rem;">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="incomplete_only" value="1" {{ $incompleteOnly ? 'checked' : '' }}>
                    Incomplete only
                </label>
            </div>
            <div class="form-group">
                <label for="round">Round</label>
                <select name="round" id="round">
                    <option value="">All</option>
                    @foreach($rounds as $r)
                        <option value="{{ $r }}" {{ (string)$roundFilter === (string)$r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <h2>Match list</h2>
        @if(empty($bouts))
            <p>No bouts on your mat@if($incompleteOnly || $roundFilter) with the current filters@endif.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Bout</th>
                        <th>Division / weight</th>
                        <th>Round</th>
                        <th>Wrestlers</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bouts as $b)
                        <tr>
                            <td><a href="{{ route('mat.bout.show', ['boutId' => $b->id]) }}">Bout {{ $b->id }}</a></td>
                            <td>{{ $b->division_name }} {{ $b->weight }}</td>
                            <td>{{ $b->round }}</td>
                            <td>
                                {{ $b->wr1->wr_first_name }} {{ $b->wr1->wr_last_name }}@if($b->wr1->wr_club) ({{ $b->wr1->wr_club }})@endif<br>
                                {{ $b->wr2->wr_first_name }} {{ $b->wr2->wr_last_name }}@if($b->wr2->wr_club) ({{ $b->wr2->wr_club }})@endif
                            </td>
                            <td>{{ $b->completed ? 'Completed' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
@endif

@if($matNumber === null)
<p style="margin-top: 1.5rem;"><a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></p>
<form id="logout-form" action="{{ url('/logout') }}" method="post" style="display: none;">@csrf</form>
@endif
@endsection
