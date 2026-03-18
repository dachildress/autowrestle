@extends('layouts.autowrestle')

@section('title', 'Edit Number Scheme – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Edit Number Scheme</h1>
        <p class="mb-6"><a href="{{ route('manage.number-schemes.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Back to Number Schemes</a></p>

        @if($errors->any())
            <ul class="mb-4 text-red-600 text-sm list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        @endif

        <form method="post" action="{{ route('manage.number-schemes.update', [$tournament->id, $scheme->id]) }}" class="space-y-5">
            @csrf

            @php
                $matNumbers = old('mat_numbers', $scheme->all_mats ? [] : ($scheme->mat_numbers ?? []));
                $roundNumbers = old('round_numbers', $scheme->all_rounds ? [] : ($scheme->round_numbers ?? []));
                $groupIds = old('group_ids', $scheme->all_groups ? [] : $scheme->schemeGroups->map(fn ($sg) => $sg->division_id . ',' . $sg->group_id)->all());
            @endphp

            <div>
                <label for="scheme_name" class="block text-sm font-medium text-slate-700">Scheme Name <span class="text-red-600">*</span></label>
                <input type="text" name="scheme_name" id="scheme_name" value="{{ old('scheme_name', $scheme->scheme_name) }}" maxlength="100" required class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm text-slate-900">
                @error('scheme_name') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-slate-700 mb-2">Mats <span class="text-red-600">*</span></span>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="all_mats" value="1" {{ old('all_mats', $scheme->all_mats ? '1' : '0') === '1' ? 'checked' : '' }} class="rounded-full border-slate-300 text-aw-accent">
                        <span class="text-sm text-slate-700">All Mats</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="all_mats" value="0" {{ old('all_mats', $scheme->all_mats ? '1' : '0') === '0' ? 'checked' : '' }} class="rounded-full border-slate-300 text-aw-accent">
                        <span class="text-sm text-slate-700">Select:</span>
                    </label>
                </div>
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach($mats as $m)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="mat_numbers[]" value="{{ $m->mat_number }}" {{ in_array($m->mat_number, $matNumbers) ? 'checked' : '' }} class="rounded border-slate-300 text-aw-accent">
                            <span class="text-sm text-slate-700">{{ $m->display_name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('mat_numbers') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-slate-700 mb-2">Groups <span class="text-red-600">*</span></span>
                <div class="flex flex-wrap gap-4 mb-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="all_groups" value="1" {{ old('all_groups', $scheme->all_groups ? '1' : '0') === '1' ? 'checked' : '' }} class="rounded-full border-slate-300 text-aw-accent">
                        <span class="text-sm text-slate-700">All Groups</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="all_groups" value="0" {{ old('all_groups', $scheme->all_groups ? '1' : '0') === '0' ? 'checked' : '' }} class="rounded-full border-slate-300 text-aw-accent">
                        <span class="text-sm text-slate-700">Select:</span>
                    </label>
                </div>
                <div class="max-h-48 overflow-y-auto rounded-md border border-slate-200 bg-slate-50 p-3 space-y-2">
                    @foreach($tournament->divisions as $div)
                        <div>
                            <p class="text-sm font-medium text-slate-700">{{ $div->DivisionName }}</p>
                            <div class="flex flex-wrap gap-2 mt-1 ml-2">
                                @foreach($div->divGroups as $g)
                                    <label class="inline-flex items-center gap-1 cursor-pointer">
                                        <input type="checkbox" name="group_ids[]" value="{{ $div->id }},{{ $g->id }}" {{ in_array($div->id.','.$g->id, $groupIds) ? 'checked' : '' }} class="rounded border-slate-300 text-aw-accent">
                                        <span class="text-sm text-slate-600">{{ $g->Name ?? 'Group ' . $g->id }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('group_ids') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-slate-700 mb-2">Rounds <span class="text-red-600">*</span></span>
                <div class="flex flex-wrap gap-4 mb-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="all_rounds" value="1" {{ old('all_rounds', $scheme->all_rounds ? '1' : '0') === '1' ? 'checked' : '' }} class="rounded-full border-slate-300 text-aw-accent">
                        <span class="text-sm text-slate-700">All Rounds</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="all_rounds" value="0" {{ old('all_rounds', $scheme->all_rounds ? '1' : '0') === '0' ? 'checked' : '' }} class="rounded-full border-slate-300 text-aw-accent">
                        <span class="text-sm text-slate-700">Select:</span>
                    </label>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach($rounds as $r)
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="round_numbers[]" value="{{ $r }}" {{ in_array($r, $roundNumbers) ? 'checked' : '' }} class="rounded border-slate-300 text-aw-accent">
                            <span class="text-sm text-slate-700">Round {{ $r }}</span>
                        </label>
                    @endforeach
                </div>
                @error('round_numbers') <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="match_ids" class="block text-sm font-medium text-slate-700">Match Ids</label>
                <input type="text" name="match_ids" id="match_ids" value="{{ old('match_ids', $scheme->match_ids) }}" maxlength="500" placeholder="Optional" class="mt-1 block w-full rounded-md border border-slate-300 py-2 px-3 text-sm text-slate-900">
            </div>

            <div>
                <label for="start_at" class="block text-sm font-medium text-slate-700">Start At <span class="text-red-600">*</span></label>
                <input type="number" name="start_at" id="start_at" value="{{ old('start_at', $scheme->start_at) }}" min="1" max="9999" required class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 py-2 px-3 text-sm text-slate-900">
            </div>

            <div>
                <label for="skip_byes" class="block text-sm font-medium text-slate-700">Skip Byes <span class="text-red-600">*</span></label>
                <select name="skip_byes" id="skip_byes" required class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 py-2 px-3 text-sm text-slate-900">
                    <option value="1" {{ old('skip_byes', $scheme->skip_byes ? '1' : '0') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ old('skip_byes', $scheme->skip_byes ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="same_mat_per_bracket" value="1" {{ old('same_mat_per_bracket', $scheme->same_mat_per_bracket) ? 'checked' : '' }} class="rounded border-slate-300 text-aw-accent">
                    <span class="text-sm font-medium text-slate-700">Keep same bracket on same mat</span>
                </label>
                <p class="mt-1 text-sm text-slate-500">When checked, all bouts for a bracket (all rounds) are assigned to one mat instead of rotating across mats.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <x-button type="submit" variant="primary">Update</x-button>
                <a href="{{ route('manage.number-schemes.index', $tournament->id) }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </x-card>
</div>
@endsection
