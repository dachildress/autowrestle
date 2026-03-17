@extends('layouts.autowrestle')

@section('title', 'Add Club')

@section('content')
<x-card :padding="true" class="max-w-md">
    <h1 class="text-xl font-semibold text-slate-900 mb-2">Add Club</h1>
    <p class="mb-4"><a href="{{ route('wrestlers.create') }}" class="text-aw-accent hover:underline">← Back to Add Wrestler</a></p>

    @if($errors->any())
        <ul class="mb-4 text-red-600 text-sm list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    @endif

    <form method="post" action="{{ route('wrestlers.clubs.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="Club" class="block text-sm font-medium text-slate-700">Club / Team name <span class="text-red-600">*</span></label>
            <input type="text" name="Club" id="Club" value="{{ old('Club') }}" maxlength="255" required class="mt-1 block w-full rounded-md border-slate-300 text-sm">
        </div>
        <div class="flex gap-3">
            <button type="submit" class="inline-flex items-center rounded-md bg-aw-primary px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Add Club</button>
            <a href="{{ route('wrestlers.create') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</x-card>
@endsection
