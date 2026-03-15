@extends('layouts.autowrestle')

@section('title', 'Site content')
@section('panel_title', 'Site content')

@section('content')
<p><a href="{{ route('manage.tournaments.index') }}" class="text-aw-accent hover:underline">← Back to Manage</a></p>

@if(session('success'))
    <x-alert variant="success" class="mt-4">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert variant="error" class="mt-4">{{ session('error') }}</x-alert>
@endif

<p class="mt-4 text-slate-600">Edit text and images used on the public site. Changes appear on the homepage, registration, and other pages.</p>

<div class="mt-6 space-y-3">
    @foreach($items as $item)
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div>
                <span class="font-medium text-slate-900">{{ $item['label'] }}</span>
                <span class="ml-2 text-slate-500 text-sm">({{ $item['key'] }})</span>
                @if($item['type'] === 'image')
                    <x-badge :variant="$item['has_value'] ? 'success' : 'default'" class="ml-2">{{ $item['has_value'] ? 'Image set' : 'No image' }}</x-badge>
                @endif
            </div>
            <x-button variant="secondary" href="{{ route('manage.content.edit', ['key' => $item['key']]) }}">Edit</x-button>
        </div>
    @endforeach
</div>
@endsection
