@extends('layouts.mat')

@section('title', 'Results – Bout ' . ($boutNumber ?? $boutId))

@section('content')
@include('mat.nav', ['boutId' => $boutId, 'current' => 'results'])

@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if(session('error'))<p class="error">{{ session('error') }}</p>@endif
@if(session('info'))<p class="success">{{ session('info') }}</p>@endif

<div style="max-width: 520px; margin: 0 auto;">
    <h2 style="text-align: center; margin-bottom: 1rem;">
        <a href="{{ route('mat.bout.show', ['boutId' => $boutId]) }}" style="color: #2c3e50;">{{ $divisionName }} (Bout {{ $boutNumber ?? $boutId }})</a>
    </h2>

    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
        <span style="color: #c00; font-weight: 600;">{{ $redWrestler->wr_first_name }} {{ $redWrestler->wr_last_name }} {{ $redWrestler->wr_club ?: 'Unattached' }}</span>
        <span style="color: #080; font-weight: 600;">{{ $greenWrestler->wr_first_name }} {{ $greenWrestler->wr_last_name }} {{ $greenWrestler->wr_club ?: 'Unattached' }}</span>
    </div>

    <form method="post" action="{{ route('mat.bout.results.save', ['boutId' => $boutId]) }}">
        @csrf

        <div style="margin-bottom: 1rem;">
            <label for="winner_id" style="display: block; margin-bottom: 0.25rem; font-weight: 600;">Select a winner</label>
            <select name="winner_id" id="winner_id" style="width: 100%; padding: 0.5rem;">
                <option value="">— No winner / tie —</option>
                <option value="{{ $redWrestler->id }}" {{ old('winner_id', (string)$defaultWinnerId) == (string)$redWrestler->id ? 'selected' : '' }}>Red: {{ $redWrestler->wr_first_name }} {{ $redWrestler->wr_last_name }}</option>
                <option value="{{ $greenWrestler->id }}" {{ old('winner_id', (string)$defaultWinnerId) == (string)$greenWrestler->id ? 'selected' : '' }}>Green: {{ $greenWrestler->wr_first_name }} {{ $greenWrestler->wr_last_name }}</option>
            </select>
        </div>

        <div style="margin-bottom: 1rem;">
            <label for="result_type" style="display: block; margin-bottom: 0.25rem; font-weight: 600;">Result type</label>
            <select name="result_type" id="result_type" style="width: 100%; padding: 0.5rem;">
                @foreach($resultTypes as $value => $label)
                    <option value="{{ $value }}" {{ old('result_type', $defaultResultType ?? $state->result_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('result_type')
                <p class="error" style="margin-top: 0.25rem; font-size: 0.9rem;">The wrestlers cannot both be &quot;Unknown&quot; if you want to save the result as anything other than a double forfeit.</p>
            @enderror
        </div>

        <fieldset style="margin-bottom: 1.5rem; padding: 1rem; border: 1px solid #ddd; border-radius: 4px;">
            <legend style="font-weight: 600;">Match end time</legend>
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                <input type="number" name="month" value="{{ old('month', $defaultMonth) }}" min="1" max="12" placeholder="MM" style="width: 3.5em; padding: 0.35rem;">
                <span>/</span>
                <input type="number" name="day" value="{{ old('day', $defaultDay) }}" min="1" max="31" placeholder="DD" style="width: 3.5em; padding: 0.35rem;">
                <span>/</span>
                <input type="number" name="year" value="{{ old('year', $defaultYear) }}" min="2000" max="2100" placeholder="YYYY" style="width: 4.5em; padding: 0.35rem;">
                <span style="margin-left: 0.5rem;">@</span>
                <input type="number" name="hour" value="{{ old('hour', $defaultHour) }}" min="1" max="12" placeholder="H" style="width: 3em; padding: 0.35rem;">
                <span>:</span>
                <input type="number" name="minute" value="{{ old('minute', $defaultMinute) }}" min="0" max="59" placeholder="MM" style="width: 3em; padding: 0.35rem;">
                <select name="am_pm" style="padding: 0.35rem;">
                    <option value="am" {{ old('am_pm', $defaultAmPm) === 'am' ? 'selected' : '' }}>a.m.</option>
                    <option value="pm" {{ old('am_pm', $defaultAmPm) === 'pm' ? 'selected' : '' }}>p.m.</option>
                </select>
            </div>
            @if($errors->any() && !$errors->has('result_type'))
                <p class="error" style="margin-top: 0.5rem; font-size: 0.9rem;">Please correct the date/time.</p>
            @endif
        </fieldset>

        <div style="text-align: center;">
            <button type="submit" class="btn btn-success" style="padding: 0.5rem 1.5rem;">Save Result</button>
        </div>
    </form>
</div>
@endsection
