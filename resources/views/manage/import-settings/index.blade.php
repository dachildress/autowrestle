@extends('layouts.autowrestle')

@section('title', 'Import Settings – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-4xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Import Settings</h1>
        <p class="mb-4 text-slate-600">Copy configuration from another tournament into <strong>{{ $tournament->TournamentName }}</strong>. This does not copy wrestlers, brackets, or matches.</p>
        <p class="mb-6">
            <a href="{{ route('manage.tournaments.show', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Match Board</a>
        </p>

        @if(session('success'))
            <p class="mb-4 rounded-md bg-green-50 px-3 py-2 text-sm text-green-800">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        <x-card :padding="true" class="mb-6 py-3">
            <form method="get" action="{{ route('manage.import-settings.index', $tournament->id) }}" class="flex flex-wrap items-end gap-x-3 gap-y-2">
                <div class="min-w-0 flex-1 basis-48">
                    <label for="name" class="sr-only">Tournament name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $filters['name'] ?? '') }}"
                        placeholder="Name"
                        class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="w-32 shrink-0">
                    <label for="date_from" class="sr-only">From date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ old('date_from', $filters['date_from'] ?? '') }}"
                        class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="w-32 shrink-0">
                    <label for="date_to" class="sr-only">To date</label>
                    <input type="date" id="date_to" name="date_to" value="{{ old('date_to', $filters['date_to'] ?? '') }}"
                        class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <x-button type="submit" variant="primary" class="!py-1.5 !text-sm">Filter</x-button>
                    <a href="{{ route('manage.import-settings.index', $tournament->id) }}" class="text-sm text-slate-600 underline hover:text-slate-900">Clear</a>
                </div>
            </form>
        </x-card>

        @if($tournaments->isEmpty())
            <p class="text-slate-600">No other tournaments found. Create or get access to another tournament to import settings from it.</p>
        @else
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-slate-100 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Name</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-900">Date</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($tournaments as $t)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-900">{{ $t->TournamentName }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $t->TournamentDate ? $t->TournamentDate->format('M j, Y') : '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <x-button href="{{ route('manage.import-settings.from', [$tournament->id, $t->id]) }}" variant="primary">Import from this tournament</x-button>
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
