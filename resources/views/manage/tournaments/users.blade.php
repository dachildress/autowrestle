@extends('layouts.autowrestle')

@section('title', 'Tournament access – ' . $tournament->TournamentName)
@section('panel_title', 'Tournament access')

@section('content')
<p class="mb-4"><a href="{{ route('manage.view.summary', $tournament->id) }}" class="text-aw-accent hover:underline">← Back to tournament</a></p>

<p class="mb-4 text-slate-600">Users listed below can manage this tournament (divisions, groups, brackets, scoring, reports). To add access, enter their email address.</p>

@if(session('success'))<p class="success mb-4">{{ session('success') }}</p>@endif
@if(session('error'))<p class="error mb-4">{{ session('error') }}</p>@endif
@if(session('info'))<p class="mb-4 text-amber-700">{{ session('info') }}</p>@endif

<x-card title="Add user access" class="mb-6">
    <form method="post" action="{{ route('manage.tournaments.users.store', $tournament->id) }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div class="min-w-[220px]">
            <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
            <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-md border-slate-300 text-sm" placeholder="user@example.com">
        </div>
        <button type="submit" class="inline-flex items-center rounded-md bg-aw-primary px-3 py-2 text-sm font-medium text-white hover:bg-aw-primary/90">Add access</button>
    </form>
</x-card>

<x-card title="Users with access">
    @if($tournament->users->isEmpty())
        <p class="text-slate-600">No users assigned yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-100">
                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Name</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-900">Email</th>
                        <th class="px-4 py-2 text-right font-semibold text-slate-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($tournament->users as $u)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 text-slate-900">{{ $u->name }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $u->email }}</td>
                        <td class="px-4 py-2 text-right">
                            @if($u->id !== auth()->id())
                                <form method="post" action="{{ route('manage.tournaments.users.remove', $tournament->id) }}" class="inline" onsubmit="return confirm('Remove this user\'s access to the tournament?');">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                    <button type="submit" class="text-red-600 hover:underline">Remove</button>
                                </form>
                            @else
                                <span class="text-slate-400">(you)</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
@endsection
