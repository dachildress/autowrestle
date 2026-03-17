@extends('layouts.autowrestle')

@section('title', 'Search for Wrestler – ' . $tournament->TournamentName)
@section('panel_title', 'Search For Wrestler')

@section('content')
<p>{{ $tournament->TournamentName }} — {{ $tournament->TournamentDate->format('m-d-Y') }}</p>

<form method="get" action="{{ route('mybouts.search', $tid) }}" class="form-horizontal">
    <div class="form-group">
        <label for="name">Last Name</label>
        <input id="name" name="name" type="text" placeholder="Last Name" value="{{ old('name', request('name')) }}" class="form-control" style="max-width: 200px;">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
</form>

@if(isset($wrestlers))
    @if($wrestlers->isEmpty())
        <p>No wrestlers found with that last name.</p>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Name</th>
                    <th>Club</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wrestlers as $w)
                    <tr>
                        <td><a href="{{ route('mybouts.show', ['tid' => $tid, 'wid' => $w->id]) }}" class="btn btn-info">Select</a></td>
                        <td>{{ $w->wr_first_name }} {{ $w->wr_last_name }}</td>
                        <td>{{ $w->wr_club }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endif

<p style="margin-top: 1rem;"><a href="{{ route('tournaments.show', $tid) }}">Back to tournament</a></p>
@endsection
