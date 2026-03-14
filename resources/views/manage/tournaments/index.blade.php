@extends('layouts.autowrestle')

@section('title', 'Manage Tournaments')
@section('panel_title', 'Manage a Tournament')

@section('content')
@if(auth()->user()->isAdmin())
    <p><a href="{{ route('manage.scorers.index') }}">Scorer users</a></p>
@endif
@if($tournaments->isEmpty())
    <p>You are not assigned to manage any tournaments.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($tournaments as $t)
            <tr>
                <td>{{ $t->TournamentName }}</td>
                <td>{{ $t->TournamentDate->format('M j, Y') }}</td>
                <td><a href="{{ route('manage.view.summary', $t->id) }}" class="btn">Manage</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
