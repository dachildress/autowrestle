@extends('layouts.autowrestle')

@section('title', 'Home')

@section('content')
<div class="row">
    <div class="col col-md-3">
        <div class="panel">
            <div class="panel-heading">Welcome</div>
            <div class="panel-body">
                Welcome to Autowrestle. We value your input. Please let us know how we can better serve you and your team.
            </div>
            <div class="panel-heading">Facebook Tags</div>
            <div class="panel-body">
                Tag Autowrestle on Facebook and we will display your photos on our home page!
            </div>
        </div>
    </div>
    <div class="col col-md-6">
        <div class="panel">
            <div class="panel-heading">How To</div>
            <div class="panel-body">
                <ul>
                    <li>Create an Autowrestle account to manage your wrestlers.</li>
                    <li>Login using your email address and password.</li>
                    <li>Click your name in the upper right to Manage your wrestlers.</li>
                    <li>Update your wrestler weight before you register for a tournament.</li>
                    <li>Click on a tournament.</li>
                    <li>Click Add Wrestler on each wrestler you want to enter into the tournament.</li>
                </ul>
                Like us on Facebook and tag Autowrestle in your pictures to have them posted on our site!
            </div>
            <div class="panel-heading">Upcoming Tournaments</div>
            <div class="panel-body">
                @forelse($tournaments as $tournament)
                    <article style="margin-bottom: 1rem;">
                        @auth
                            <a href="{{ route('tournaments.register', $tournament->id) }}">
                                <h2 style="margin: 0 0 0.5rem;">{{ $tournament->TournamentName }} — Date: {{ $tournament->TournamentDate->format('D M d, Y') }}</h2>
                            </a>
                        @else
                            <h2 style="margin: 0 0 0.5rem;">{{ $tournament->TournamentName }} — Date: {{ $tournament->TournamentDate->format('D M d, Y') }}</h2>
                        @endauth
                        <div>{!! nl2br(e($tournament->message)) !!}</div>
                        @if($tournament->link)
                            <a href="{{ asset('flyers/' . $tournament->link) }}" target="_blank">Tournament Flyer</a>
                        @endif
                        @if($tournament->ViewWrestlers)
                            <p><a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-block">View Registered Wrestlers</a></p>
                        @endif
                    </article>
                @empty
                    <p>No upcoming tournaments at this time.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col col-md-3">
        <div class="panel">
            <div class="panel-heading">Stats/Rankings</div>
            <div class="panel-body">
                <table class="table-bordered" style="width:100%; margin-bottom: 1rem;">
                    <caption style="font-weight: bold;">Quick Pins</caption>
                    <tr><td>#</td><td>Name</td><td>Club</td><td>Time</td></tr>
                    <tr><td>1</td><td>Your Child</td><td>Your Club</td><td>.15</td></tr>
                </table>
                <table class="table-bordered" style="width:100%;">
                    <caption style="font-weight: bold;">Top Rankings</caption>
                    <tr><td>#</td><td>Name</td><td>Club</td><td>W</td><td>L</td></tr>
                    <tr><td>1</td><td>Your Child</td><td>Your Club</td><td>15</td><td>2</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
