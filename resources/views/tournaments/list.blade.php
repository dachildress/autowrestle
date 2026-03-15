@extends('layouts.autowrestle')

@section('title', 'Tournaments')

@section('content')
<x-page-header title="Current Tournaments" />

@if(content('tournaments.banner'))
    <x-alert variant="info" class="mb-6">{{ content('tournaments.banner') }}</x-alert>
@endif

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
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($tournaments as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-900">{{ $t->TournamentName }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->TournamentDate->format('M j, Y') }}</td>
                        <td class="px-4 py-3"><x-badge variant="default">{{ $t->status }}</x-badge></td>
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
