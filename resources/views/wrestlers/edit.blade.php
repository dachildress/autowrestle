@extends('layouts.autowrestle')

@section('title', 'Edit Wrestler')
@section('panel_title', 'Edit Wrestler')

@section('content')
<form method="post" action="{{ route('wrestlers.update', $wrestler->id) }}" class="form-horizontal">
    @csrf
    <div class="form-group">
        <label for="wr_first_name">First Name</label>
        <input type="text" name="wr_first_name" id="wr_first_name" value="{{ old('wr_first_name', $wrestler->wr_first_name) }}" maxlength="30" required>
    </div>
    <div class="form-group">
        <label for="wr_last_name">Last Name</label>
        <input type="text" name="wr_last_name" id="wr_last_name" value="{{ old('wr_last_name', $wrestler->wr_last_name) }}" maxlength="30" required>
    </div>
    <div class="form-group">
        <label for="wr_dob">Date of Birth</label>
        <input type="text" name="wr_dob" id="wr_dob" value="{{ old('wr_dob', $wrestler->wr_dob?->format('m/d/Y')) }}" placeholder="MM/DD/YYYY">
    </div>
    <div class="form-group">
        <label for="wr_grade">Grade</label>
        <input type="text" name="wr_grade" id="wr_grade" value="{{ old('wr_grade', $wrestler->wr_grade) }}" maxlength="10" required>
    </div>
    <div class="form-group">
        <label for="wr_club">Club</label>
        <input type="text" name="wr_club" id="wr_club" value="{{ old('wr_club', $wrestler->wr_club) }}" maxlength="30" required>
    </div>
    <div class="form-group">
        <label for="wr_years">Years Experience</label>
        <input type="number" name="wr_years" id="wr_years" value="{{ old('wr_years', $wrestler->wr_years) }}" min="0" max="30" required>
    </div>
    <div class="form-group">
        <label for="wr_age">Age</label>
        <input type="number" name="wr_age" id="wr_age" value="{{ old('wr_age', $wrestler->wr_age) }}" min="3" max="19" required>
    </div>
    <div class="form-group">
        <label for="wr_weight">Weight</label>
        <input type="number" name="wr_weight" id="wr_weight" value="{{ old('wr_weight', $wrestler->wr_weight) }}" min="0" max="500" step="0.1">
    </div>
    <div class="form-group">
        <label for="usawnumber">USAW#</label>
        <input type="text" name="usawnumber" id="usawnumber" value="{{ old('usawnumber', $wrestler->usawnumber) }}">
    </div>
    <div class="form-group">
        <label for="coach_name">Coach Name</label>
        <input type="text" name="coach_name" id="coach_name" value="{{ old('coach_name', $wrestler->coach_name ?? '') }}" maxlength="50">
    </div>
    <div class="form-group">
        <label for="coach_phone">Coach Phone</label>
        <input type="text" name="coach_phone" id="coach_phone" value="{{ old('coach_phone', $wrestler->coach_phone ?? '') }}" maxlength="14">
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('wrestlers.index') }}" class="btn btn-danger">back</a>
    </div>
</form>
@endsection
