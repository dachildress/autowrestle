@extends('layouts.autowrestle')

@section('title', 'Manage Tournaments')
@section('panel_title', 'Manage a Tournament')

@section('content')
<x-page-header title="Manage a Tournament">
    <x-slot:actions>
        <x-button href="{{ route('manage.tournaments.create') }}" variant="primary">Create tournament</x-button>
        @if(auth()->user()->isAdmin())
            <x-button href="{{ route('manage.scorers.index') }}" variant="ghost">Mat Users</x-button>
            <x-button href="{{ route('manage.content.index') }}" variant="secondary">Site content</x-button>
        @endif
    </x-slot:actions>
</x-page-header>

@if(isset($pendingApproval) && $pendingApproval->isNotEmpty())
    <x-card title="Pending approval" class="mb-6">
        <p class="text-sm text-slate-600 mb-4">These tournaments were created by non-admin users and need your approval to appear on the public site.</p>
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100">
                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Name</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Date</th>
                        <th class="px-4 py-2 text-right font-semibold text-slate-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($pendingApproval as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 text-slate-900">{{ $t->TournamentName }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $t->TournamentDate->format('M j, Y') }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="post" action="{{ route('manage.tournaments.approve', $t->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-aw-accent hover:underline">Approve</button>
                            </form>
                            ·
                            <a href="{{ route('manage.view.summary', $t->id) }}" class="text-aw-accent hover:underline">Manage</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endif

@if($tournaments->isEmpty())
    <x-card>
        <p class="text-slate-600">You are not assigned to manage any tournaments. Create one above or ask an administrator to give you access to an existing tournament.</p>
    </x-card>
@else
    <x-card :padding="true">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100">
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Name</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Date</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($tournaments as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-900">
                            {{ $t->TournamentName }}
                            @if($t->pending_approval)
                                <span class="ml-2 rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-800">Pending approval</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $t->TournamentDate->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <x-button href="{{ route('manage.view.summary', $t->id) }}" variant="primary">Manage</x-button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endif
@endsection
