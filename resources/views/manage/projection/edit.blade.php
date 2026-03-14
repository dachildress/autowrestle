@extends('layouts.autowrestle')

@section('title', 'Edit projection view – ' . $tournament->TournamentName)
@section('panel_title', 'Edit: ' . $view->name)

@section('content')
<p><a href="{{ route('manage.projection.index', $tournament->id) }}">← Back to projection views</a></p>

<form method="post" action="{{ route('manage.projection.update', [$tournament->id, $view->id]) }}" class="form-horizontal">
    @csrf
    @method('PATCH')
    <div class="form-group">
        <label for="name">View name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $view->name) }}" required>
        @error('name')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="wrestlers_per_mat">Bouts per mat to show</label>
        <input type="number" name="wrestlers_per_mat" id="wrestlers_per_mat" value="{{ old('wrestlers_per_mat', $view->wrestlers_per_mat) }}" min="1" max="20">
        @error('wrestlers_per_mat')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label>Groups to include</label>
        <div style="max-height: 240px; overflow-y: auto; border: 1px solid #ecf0f1; padding: 8px; border-radius: 4px;">
            @foreach($groups as $g)
                <label style="display: block; margin: 4px 0;">
                    <input type="checkbox" name="groups[]" value="{{ $g->key }}" {{ in_array($g->key, old('groups', $selectedKeys)) ? 'checked' : '' }}>
                    {{ $g->label }}
                </label>
            @endforeach
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('manage.projection.index', $tournament->id) }}" class="btn">Cancel</a>
    </div>
</form>
@endsection
