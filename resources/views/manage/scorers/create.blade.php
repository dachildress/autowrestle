@extends('layouts.autowrestle')

@section('title', 'Add mat user')
@section('panel_title', 'Add mat user')

@section('content')
<p><a href="{{ route('manage.scorers.index') }}">← Back to Mat Users</a></p>

<form method="post" action="{{ route('manage.scorers.store') }}" class="form-horizontal">
    @csrf
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="{{ old('username') }}" required maxlength="40" autocomplete="username">
        @error('username')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" required>
        @error('email')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
        @error('password')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="password_confirmation">Confirm password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="mat_number">Mat number</label>
        <input type="number" name="mat_number" id="mat_number" value="{{ old('mat_number') }}" min="1" max="255" placeholder="Optional">
        @error('mat_number')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label>
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" {{ old('active', '1') === '1' ? 'checked' : '' }}>
            Active
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create scorer</button>
        <a href="{{ route('manage.scorers.index') }}" class="btn">Cancel</a>
    </div>
</form>
@endsection
