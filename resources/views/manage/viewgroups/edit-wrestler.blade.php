@extends('layouts.autowrestle')

@section('title', 'Edit Wrestler – ' . $tournament->TournamentName)
@section('panel_title', 'Edit Wrestler')

@section('content')
@if(!empty($returnUrl))
    <p><a href="{{ $returnUrl }}">← Back</a></p>
@elseif($did && $gid)
    <p><a href="{{ route('manage.viewgroups.show', [$tournament->id, $did, $gid]) }}">← Back to group</a></p>
@else
    <p><a href="{{ route('manage.viewgroups.index', $tournament->id) }}">← Back to View Groups</a></p>
@endif

@if(session('success'))<p class="success">{{ session('success') }}</p>@endif

<form method="post" action="{{ route('manage.viewgroups.updateWrestler', [$tournament->id, $tw->id]) }}" class="form-horizontal">
    @method('PUT')
    @csrf
    @if(!empty($returnUrl))<input type="hidden" name="return" value="{{ $returnUrl }}">@endif
    <div class="form-group">
        <label for="wr_first_name">First Name</label>
        <input type="text" name="wr_first_name" id="wr_first_name" value="{{ old('wr_first_name', $tw->wr_first_name) }}" maxlength="30" required>
    </div>
    <div class="form-group">
        <label for="wr_last_name">Last Name</label>
        <input type="text" name="wr_last_name" id="wr_last_name" value="{{ old('wr_last_name', $tw->wr_last_name) }}" maxlength="30" required>
    </div>
    <div class="form-group">
        <label for="wr_club">Club</label>
        <input type="text" name="wr_club" id="wr_club" value="{{ old('wr_club', $tw->wr_club) }}" maxlength="30" required>
    </div>
    <div class="form-group">
        <label for="wr_age">Age</label>
        <input type="number" name="wr_age" id="wr_age" value="{{ old('wr_age', $tw->wr_age) }}" min="3" max="19" required>
    </div>
    <div class="form-group">
        <label for="wr_grade">Grade</label>
        <input type="text" name="wr_grade" id="wr_grade" value="{{ old('wr_grade', $tw->wr_grade) }}" maxlength="10" required>
    </div>
    <div class="form-group">
        <label for="wr_weight">Weight</label>
        <input type="number" name="wr_weight" id="wr_weight" value="{{ old('wr_weight', $tw->wr_weight) }}" min="0" max="500" step="0.1" placeholder="Optional">
    </div>
    <div class="form-group">
        <label for="wr_years">Years Experience</label>
        <input type="number" name="wr_years" id="wr_years" value="{{ old('wr_years', $tw->wr_years) }}" min="0" max="30" required>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-success">Save</button>
        @if(!empty($returnUrl))
            <a href="{{ $returnUrl }}" class="btn btn-danger">Cancel</a>
        @elseif($did && $gid)
            <a href="{{ route('manage.viewgroups.show', [$tournament->id, $did, $gid]) }}" class="btn btn-danger">Cancel</a>
        @else
            <a href="{{ route('manage.viewgroups.index', $tournament->id) }}" class="btn btn-danger">Cancel</a>
        @endif
    </div>
</form>

@if($errors->any())
    <ul class="error-list">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
@endif
@endsection
