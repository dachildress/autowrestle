@extends('layouts.autowrestle')

@section('title', 'Dev Import')
@section('panel_title', 'Dev Import')

@section('content')
<x-card title="Select a Tournament">
    <p class="mb-4 text-slate-600">Choose the tournament you want to import development users/wrestlers into.</p>

    <table class="w-full border-collapse text-sm">
        <thead>
            <tr class="border-b border-slate-200 text-left">
                <th class="py-2 pr-4">Tournament</th>
                <th class="py-2 pr-4">Date</th>
                <th class="py-2 pr-4"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($tournaments as $t)
                <tr class="border-b border-slate-100">
                    <td class="py-2 pr-4">{{ $t->TournamentName }}</td>
                    <td class="py-2 pr-4">{{ $t->TournamentDate?->format('m-d-Y') }}</td>
                    <td class="py-2">
                        <a href="{{ route('dev.import.users', ['tid' => $t->id]) }}" class="text-aw-accent hover:underline mr-4">Import Users</a>
                        <a href="{{ route('dev.import.wrestler', ['tid' => $t->id]) }}" class="text-aw-accent hover:underline">Import Wrestlers</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="py-4 text-slate-500">No tournaments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-card>
@endsection

