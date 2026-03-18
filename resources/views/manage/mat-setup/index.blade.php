@extends('layouts.autowrestle')

@section('title', 'Mat setup – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-3xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Mat setup</h1>
        <p class="mb-4 text-slate-600">Define the mats for this tournament. Add at least one mat before creating number schemes. You can add a constraint (e.g. "Elementary only" or "small mats") to help assign bouts.</p>
        <p class="mb-6">
            <a href="{{ route('manage.tournaments.show', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to {{ $tournament->TournamentName }}</a>
            | <a href="{{ route('manage.mat-setup.create', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">Add mat</a>
        </p>

        @if(session('success'))
            <p class="mb-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif

        @if($mats->isEmpty())
            <p class="text-slate-600">No mats defined yet. <a href="{{ route('manage.mat-setup.create', $tournament->id) }}" class="text-aw-accent hover:underline">Add a mat</a> so you can create number schemes and assign bouts to mats.</p>
        @else
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">#</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Constraint</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($mats as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-900">{{ $m->mat_number }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $m->name ?: 'Mat ' . $m->mat_number }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $m->constraint ?: '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('manage.mat-setup.edit', [$tournament->id, $m->id]) }}" class="text-aw-accent hover:underline">Edit</a>
                                    | <a href="{{ route('manage.mat-setup.destroy', [$tournament->id, $m->id]) }}" onclick="return confirm('Remove this mat?');" class="text-red-600 hover:underline">Remove</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
</div>
@endsection
