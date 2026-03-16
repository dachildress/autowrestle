@extends('layouts.autowrestle')

@section('title', 'Home')

@section('content')
{{-- Hero: editable via Site content in admin --}}
<section class="relative mb-8 overflow-hidden rounded-xl bg-aw-primary px-6 py-12 text-white md:py-16">
    @if(function_exists('site_content_image') && site_content_image('home.hero.image'))
        <img src="{{ site_content_image('home.hero.image') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30">
    @endif
    <div class="relative">
        <h1 class="text-3xl font-bold tracking-tight md:text-4xl">{{ content('home.hero.title') }}</h1>
        <p class="mt-2 text-lg text-slate-200">{{ content('home.hero.subtitle') }}</p>
    </div>
</section>

@if(content('home.features.intro'))
<p class="mb-6 text-slate-600">{{ content('home.features.intro') }}</p>
@endif

<div class="grid gap-6 md:grid-cols-3">
    <div class="md:col-span-2 space-y-6">
        <x-card title="Upcoming Tournaments">
            @forelse($tournaments as $tournament)
                <article class="border-b border-slate-200 pb-4 last:border-0 last:pb-0">
                    @auth
                        <a href="{{ route('tournaments.register', $tournament->id) }}" class="hover:text-aw-accent">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $tournament->TournamentName }} — {{ $tournament->TournamentDate->format('D M d, Y') }}</h2>
                        </a>
                    @else
                        <h2 class="text-lg font-semibold text-slate-900">{{ $tournament->TournamentName }} — {{ $tournament->TournamentDate->format('D M d, Y') }}</h2>
                    @endauth
                    <div class="mt-1 text-slate-600">{!! nl2br(e($tournament->message)) !!}</div>
                    @if($tournament->link)
                        <a href="{{ asset('flyers/' . $tournament->link) }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm text-aw-accent hover:underline">Tournament Flyer</a>
                    @endif
                    @if($tournament->ViewWrestlers)
                        <p class="mt-2">
                            <x-button href="{{ route('tournaments.show', $tournament->id) }}" variant="primary">View Registered Wrestlers</x-button>
                        </p>
                    @endif
                </article>
            @empty
                <p class="text-slate-500">No upcoming tournaments at this time.</p>
            @endforelse
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Welcome">
            <p class="text-slate-700">Welcome to AutoWrestle. We value your input. Please let us know how we can better serve you and your team.</p>
            <p class="mt-3 text-sm text-slate-500">Tag AutoWrestle on Facebook and we will display your photos on our home page!</p>
        </x-card>

        <x-card title="Stats / Rankings">
            <table class="w-full border-collapse text-sm">
                <caption class="text-left font-semibold text-slate-900">Quick Pins</caption>
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="py-1 pr-2 text-left">#</th><th class="py-1 pr-2 text-left">Name</th><th class="py-1 pr-2 text-left">Club</th><th class="py-1 text-left">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quickPins as $rank => $pin)
                    <tr class="border-b border-slate-100">
                        <td class="py-1 pr-2">{{ $rank + 1 }}</td>
                        <td class="py-1 pr-2">{{ $pin['name'] }}</td>
                        <td class="py-1 pr-2">{{ $pin['club'] }}</td>
                        <td class="py-1">{{ $pin['wrtime'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-2 text-slate-500">No pin data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <table class="mt-4 w-full border-collapse text-sm">
                <caption class="text-left font-semibold text-slate-900">Top Rankings</caption>
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="py-1 pr-2 text-left">#</th><th class="py-1 pr-2 text-left">Name</th><th class="py-1 pr-2 text-left">Club</th><th class="py-1 pr-2 text-left">W</th><th class="py-1 text-left">L</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topRankings as $rank => $row)
                    <tr class="border-b border-slate-100">
                        <td class="py-1 pr-2">{{ $rank + 1 }}</td>
                        <td class="py-1 pr-2">{{ $row['name'] }}</td>
                        <td class="py-1 pr-2">{{ $row['club'] }}</td>
                        <td class="py-1 pr-2">{{ $row['wins'] }}</td>
                        <td class="py-1">{{ $row['losses'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-2 text-slate-500">No rankings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>

        <x-card title="How To">
            <ul class="list-inside list-disc space-y-2 text-slate-700">
                <li>Create an AutoWrestle account to manage your wrestlers.</li>
                <li>Login using your email address and password.</li>
                <li>Click your name in the upper right to manage your wrestlers.</li>
                <li>Update your wrestler weight before you register for a tournament.</li>
                <li>Click on a tournament, then Add Wrestler for each wrestler you want to enter.</li>
            </ul>
            <p class="mt-4 text-sm text-slate-500">Like us on Facebook and tag AutoWrestle in your pictures to have them posted on our site!</p>
        </x-card>
    </div>
</div>
@endsection
