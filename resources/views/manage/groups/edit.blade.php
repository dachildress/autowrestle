@extends('layouts.autowrestle')

@section('title', 'Edit Group – ' . $division->DivisionName)

@section('content')
<h1>Edit Group</h1>
<p><a href="{{ route('manage.divisions.show', [$tournament->id, $division->id]) }}">← Back to {{ $division->DivisionName }}</a></p>

<form method="post" action="{{ route('manage.groups.update', [$tournament->id, $division->id, $group->id]) }}">
    @csrf
    <p>
        <label for="Name">Name</label>
        <input type="text" name="Name" id="Name" value="{{ old('Name', $group->Name) }}" maxlength="25" required>
        @error('Name') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="MinAge">Minimum Age</label>
        <input type="number" name="MinAge" id="MinAge" value="{{ old('MinAge', $group->MinAge) }}" min="3" max="19" required>
        @error('MinAge') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="MaxAge">Max Age</label>
        <input type="number" name="MaxAge" id="MaxAge" value="{{ old('MaxAge', $group->MaxAge) }}" min="3" max="19" required>
        @error('MaxAge') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="MinGrade">Minimum Grade (-1 = Pre-K, 0 = K)</label>
        <input type="number" name="MinGrade" id="MinGrade" value="{{ old('MinGrade', $group->MinGrade) }}" min="-1" max="12" required>
        @error('MinGrade') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="MaxGrade">Max Grade</label>
        <input type="number" name="MaxGrade" id="MaxGrade" value="{{ old('MaxGrade', $group->MaxGrade) }}" min="-1" max="12" required>
        @error('MaxGrade') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="MaxWeightDiff">Max Weight Diff</label>
        <input type="number" name="MaxWeightDiff" id="MaxWeightDiff" value="{{ old('MaxWeightDiff', $group->MaxWeightDiff) }}" min="0" max="20" required>
        @error('MaxWeightDiff') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="MaxPwrDiff">Max Power Diff</label>
        <input type="number" name="MaxPwrDiff" id="MaxPwrDiff" value="{{ old('MaxPwrDiff', $group->MaxPwrDiff) }}" min="0" max="30" required>
        @error('MaxPwrDiff') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="MaxExpDiff">Max Experience Diff</label>
        <input type="number" name="MaxExpDiff" id="MaxExpDiff" value="{{ old('MaxExpDiff', $group->MaxExpDiff) }}" min="0" max="30" required>
        @error('MaxExpDiff') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <label for="BracketType">Bracket Type</label>
        <input type="text" name="BracketType" id="BracketType" value="{{ old('BracketType', $group->BracketType) }}" maxlength="20">
        @error('BracketType') <span class="error">{{ $message }}</span> @enderror
    </p>
    <p>
        <button type="submit">Save</button>
        <a href="{{ route('manage.divisions.show', [$tournament->id, $division->id]) }}">Cancel</a>
    </p>
</form>
@endsection
