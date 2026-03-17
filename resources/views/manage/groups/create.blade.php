@extends('layouts.autowrestle')

@section('title', 'Add Group – ' . $division->DivisionName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-2xl">
        <h1 class="text-xl font-bold text-aw-primary mb-2">Add Group</h1>
        <p class="mb-6"><a href="{{ route('manage.divisions.show', [$tournament->id, $division->id]) }}" class="text-blue-600 hover:underline">← Back to {{ $division->DivisionName }}</a></p>

        @if($errors->any())
            <ul class="mb-4 text-red-600 text-sm list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('manage.groups.store', [$tournament->id, $division->id]) }}" class="space-y-5">
            @csrf

            <div>
                <label for="Name" class="block text-sm font-medium text-slate-700">Group Name <span class="text-red-600">*</span></label>
                <input type="text" name="Name" id="Name" value="{{ old('Name') }}" maxlength="25" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm">
                @error('Name') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-slate-700 mb-2">Gender <span class="text-red-600">*</span></span>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="gender" value="boys" {{ old('gender', 'boys') === 'boys' ? 'checked' : '' }} class="rounded-full border border-slate-300 text-aw-primary">
                        <span class="text-sm text-slate-700">Boys</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="gender" value="girls" {{ old('gender') === 'girls' ? 'checked' : '' }} class="rounded-full border border-slate-300 text-aw-primary">
                        <span class="text-sm text-slate-700">Girls</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="gender" value="coed" {{ old('gender') === 'coed' ? 'checked' : '' }} class="rounded-full border border-slate-300 text-aw-primary">
                        <span class="text-sm text-slate-700">Co-ed</span>
                    </label>
                </div>
                @error('gender') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="MinAge" class="block text-sm font-medium text-slate-700">Minimum Age <span class="text-red-600">*</span></label>
                    <input type="number" name="MinAge" id="MinAge" value="{{ old('MinAge') }}" min="3" max="19" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm">
                    @error('MinAge') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="MaxAge" class="block text-sm font-medium text-slate-700">Max Age <span class="text-red-600">*</span></label>
                    <input type="number" name="MaxAge" id="MaxAge" value="{{ old('MaxAge') }}" min="3" max="19" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm">
                    @error('MaxAge') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="MinGrade" class="block text-sm font-medium text-slate-700">Minimum Grade <span class="text-red-600">*</span></label>
                    <select name="MinGrade" id="MinGrade" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm">
                        @foreach([-1 => 'Pre-K (-1)', 0 => 'K (0)'] + array_combine(range(1, 12), range(1, 12)) as $val => $label)
                            <option value="{{ $val }}" {{ old('MinGrade') === (string)$val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('MinGrade') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="MaxGrade" class="block text-sm font-medium text-slate-700">Max Grade <span class="text-red-600">*</span></label>
                    <select name="MaxGrade" id="MaxGrade" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm">
                        @foreach([-1 => 'Pre-K (-1)', 0 => 'K (0)'] + array_combine(range(1, 12), range(1, 12)) as $val => $label)
                            <option value="{{ $val }}" {{ old('MaxGrade') === (string)$val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('MaxGrade') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="MaxWeightDiff" class="block text-sm font-medium text-slate-700">Max Weight Diff <span class="text-red-600">*</span></label>
                    <input type="number" name="MaxWeightDiff" id="MaxWeightDiff" value="{{ old('MaxWeightDiff', 10) }}" min="0" max="20" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm">
                    @error('MaxWeightDiff') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="MaxPwrDiff" class="block text-sm font-medium text-slate-700">Max Power Diff <span class="text-red-600">*</span></label>
                    <input type="number" name="MaxPwrDiff" id="MaxPwrDiff" value="{{ old('MaxPwrDiff', 10) }}" min="0" max="30" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm">
                    @error('MaxPwrDiff') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="MaxExpDiff" class="block text-sm font-medium text-slate-700">Max Experience Diff <span class="text-red-600">*</span></label>
                <select name="MaxExpDiff" id="MaxExpDiff" required class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 py-2 px-3 text-sm">
                    @foreach(range(0, 30) as $n)
                        <option value="{{ $n }}" {{ old('MaxExpDiff', '10') === (string)$n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                @error('MaxExpDiff') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="BracketType" class="block text-sm font-medium text-slate-700">Bracket Type</label>
                <select name="BracketType" id="BracketType" class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 py-2 px-3 text-sm">
                    <option value="Round Robin" {{ old('BracketType', 'Round Robin') === 'Round Robin' ? 'selected' : '' }}>Round Robin</option>
                    @foreach([4, 5, 6, 8] as $n)
                        <option value="{{ $n }}" {{ old('BracketType') === (string)$n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                @error('BracketType') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex flex-wrap gap-3 pt-4 pb-2 border-t border-slate-200 mt-6 -mx-4 px-4 pt-6 bg-white sticky bottom-0">
                <button type="submit" class="inline-flex items-center font-semibold px-4 py-2.5 rounded-md border-0 cursor-pointer shadow-sm text-white" style="background-color: #2563eb;">Save Group</button>
                <a href="{{ route('manage.divisions.show', [$tournament->id, $division->id]) }}" class="inline-flex items-center font-semibold px-4 py-2.5 rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 no-underline">Cancel</a>
            </div>
        </form>
    </x-card>
</div>
@endsection
