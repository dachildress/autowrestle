@extends('layouts.autowrestle')

@section('title', 'Tournament Locked')

@section('content')
<div style="max-width: 36rem;">
    <h1>The tournament is now locked</h1>
    <p>The tournament has been bracketed and no further changes can be made.</p>
    <p>If you feel this is in error, please contact the tournament administrator.</p>
    <p><a href="{{ route('tournaments.list') }}">Back to tournaments</a></p>
</div>
@endsection
