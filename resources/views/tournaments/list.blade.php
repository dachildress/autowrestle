@extends('layouts.autowrestle')

@section('title', 'Tournaments')
@section('panel_title', 'Current Tournaments')

@section('content')
@if($tournaments->isEmpty())
    <p>No tournaments found.</p>
@else
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($tournaments as $t)
            <tr>
                <td>{{ $t->TournamentName }}</td>
                <td>{{ $t->TournamentDate->format('M j, Y') }}</td>
                <td>{{ $t->status }}</td>
                <td><a href="{{ route('tournaments.show', $t->id) }}" class="btn">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
