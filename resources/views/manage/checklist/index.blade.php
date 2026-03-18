@extends('layouts.autowrestle')

@section('title', 'Checklist – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-3xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Tournament Checklist</h1>
        <p class="mb-4 text-slate-600">Track your progress setting up <strong>{{ $tournament->TournamentName }}</strong>. Check off steps as you complete them; some steps are auto-checked when data is present.</p>
        <p class="mb-6">
            <a href="{{ route('manage.tournaments.show', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Match Board</a>
        </p>

        @if(session('success'))
            <p class="mb-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif

        <ul class="space-y-3 list-none p-0 m-0">
            @foreach($steps as $step)
                <li class="flex items-center gap-3 py-2 border-b border-slate-100 last:border-0">
                    <form method="post" action="{{ route('manage.checklist.toggle', $tournament->id) }}" class="shrink-0">
                        @csrf
                        <input type="hidden" name="step_key" value="{{ $step['key'] }}">
                        <input type="checkbox"
                            {{ $step['is_completed'] ? 'checked' : '' }}
                            onclick="this.form.submit()"
                            class="h-4 w-4 rounded border-slate-300 text-aw-accent focus:ring-aw-accent"
                            aria-label="Toggle {{ $step['title'] }}">
                    </form>
                    <span class="shrink-0 w-6 text-slate-500 font-medium">{{ $step['number'] }}.</span>
                    <a href="{{ $step['url'] }}" class="flex-1 text-slate-900 hover:text-aw-accent hover:underline {{ $step['is_completed'] ? 'line-through text-slate-500' : '' }}">
                        {{ $step['title'] }}
                    </a>
                    @if($step['is_completed'])
                        <span class="shrink-0 rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Done</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </x-card>
</div>
@endsection
