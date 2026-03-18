@extends('layouts.autowrestle')

@section('title', 'Number Schemes – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-3xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Number Schemes</h1>
        <p class="mb-4 text-slate-600">Assign bout numbers by mats, groups, and rounds. Create a scheme for each division or group, then use "Create bouts" on the Match Board.</p>
        <p class="mb-6">
            <a href="{{ route('manage.tournaments.show', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to {{ $tournament->TournamentName }}</a>
            @if($hasMats && $hasDivisionsAndGroups)
            | <a href="{{ route('manage.number-schemes.create', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">Add Number Scheme</a>
            @endif
        </p>

        @if(session('success'))
            <p class="mb-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        @if(!$hasMats)
            <p class="mb-4 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">Add mats first in <a href="{{ route('manage.mat-setup.index', $tournament->id) }}" class="text-aw-accent hover:underline font-medium">Mat setup</a> before you can create number schemes.</p>
        @endif
        @if($hasMats && !$hasDivisionsAndGroups)
            <p class="mb-4 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">Add <a href="{{ route('manage.divisions.index', $tournament->id) }}" class="text-aw-accent hover:underline font-medium">divisions and groups</a> before you can create number schemes.</p>
        @endif

        @if($schemes->isEmpty())
            <p class="text-slate-600">No number schemes yet. @if($hasMats && $hasDivisionsAndGroups)<a href="{{ route('manage.number-schemes.create', $tournament->id) }}" class="text-aw-accent hover:underline">Add one</a> so you can create bouts for a division.@else Add mats and divisions/groups first (see above).@endif</p>
        @else
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Scheme Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Start At</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Skip Byes</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Mats</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Groups</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Rounds</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($schemes as $s)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-900">{{ $s->scheme_name }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $s->start_at }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $s->skip_byes ? 'Yes' : 'No' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $s->all_mats ? 'All Mats' : implode(', ', $s->mat_numbers ?? []) }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $s->all_groups ? 'All Groups' : $s->schemeGroups->count() . ' selected' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $s->all_rounds ? 'All Rounds' : implode(', ', array_map(fn ($r) => 'R' . $r, $s->round_numbers ?? [])) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('manage.number-schemes.edit', [$tournament->id, $s->id]) }}" class="text-aw-accent hover:underline">Edit</a>
                                    | <a href="{{ route('manage.number-schemes.destroy', [$tournament->id, $s->id]) }}" onclick="return confirm('Delete this number scheme?');" class="text-red-600 hover:underline">Delete</a>
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
