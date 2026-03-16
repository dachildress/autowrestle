@extends('layouts.autowrestle')

@section('title', 'Tournaments')

@section('content')
<x-page-header title="Current Tournaments" />

@if(content('tournaments.banner'))
    <x-alert variant="info" class="mb-6">{{ content('tournaments.banner') }}</x-alert>
@endif

<x-card :padding="true" class="mb-4 py-3">
    <form method="get" action="{{ route('tournaments.list') }}" class="flex flex-wrap items-end gap-x-3 gap-y-2">
        <div class="min-w-0 flex-1 basis-24">
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
        <div class="w-28 shrink-0">
            <label for="city" class="sr-only">City</label>
            <input type="text" id="city" name="city" value="{{ old('city', $filters['city'] ?? '') }}"
                placeholder="City"
                class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div class="w-20 shrink-0">
            <label for="state" class="sr-only">State</label>
            <input type="text" id="state" name="state" value="{{ old('state', $filters['state'] ?? '') }}"
                placeholder="State"
                class="block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <x-button type="submit" variant="primary" class="!py-1.5 !text-sm">Filter</x-button>
            <a href="{{ route('tournaments.list') }}" class="text-sm text-slate-600 underline hover:text-slate-900">Clear</a>
        </div>
    </form>
</x-card>

@if($tournaments->isEmpty())
    <x-card>
        <p class="text-slate-600">No tournaments found.</p>
    </x-card>
@else
    <x-card :padding="true">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Date</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">City</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">State</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($tournaments as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-900">{{ $t->TournamentName }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->TournamentDate->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->city ?? '–' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->state ?? '–' }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-button href="{{ route('tournaments.show', $t->id) }}" variant="secondary">View</x-button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endif
@endsection
