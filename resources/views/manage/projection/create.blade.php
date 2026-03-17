@extends('layouts.autowrestle')

@section('title', 'Add projection view – ' . $tournament->TournamentName)
@section('panel_title', 'Add projection view')

@section('content')
<p><a href="{{ route('manage.projection.index', $tournament->id) }}">← Back to projection views</a></p>

<form method="post" action="{{ route('manage.projection.store', $tournament->id) }}" class="form-horizontal">
    @csrf
    <div class="form-group">
        <label for="name">View name</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Gym A, Main mats">
        @error('name')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="wrestlers_per_mat">Bouts per mat to show</label>
        <input type="number" name="wrestlers_per_mat" id="wrestlers_per_mat" value="{{ old('wrestlers_per_mat', 4) }}" min="1" max="20">
        <span class="help-inline">Current bout (green) + next bouts (yellow then white).</span>
        @error('wrestlers_per_mat')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label>Groups to include</label>
        <p class="help-inline">Select which division/groups this view shows (e.g. one gym’s groups).</p>
        <div style="max-height: 240px; overflow-y: auto; border: 1px solid #ecf0f1; padding: 8px; border-radius: 4px;">
            @foreach($groups as $g)
                <label style="display: block; margin: 4px 0;">
                    <input type="checkbox" name="groups[]" value="{{ $g->key }}" {{ in_array($g->key, old('groups', [])) ? 'checked' : '' }}>
                    {{ $g->label }}
                </label>
            @endforeach
        </div>
        @if(empty($groups))
            <p class="error">No groups found. Add divisions and groups first.</p>
        @endif
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create view</button>
        <a href="{{ route('manage.projection.index', $tournament->id) }}" class="btn">Cancel</a>
    </div>
</form>
@endsection
