@extends('layouts.autowrestle')

@section('title', 'Edit mat user – ' . $scorer->username)
@section('panel_title', 'Edit mat user: ' . $scorer->username)

@section('content')
<p><a href="{{ route('manage.scorers.index') }}">← Back to Mat Users</a></p>

@if(session('success'))
    <p class="success">{{ session('success') }}</p>
@endif

<form method="post" action="{{ route('manage.scorers.update', $scorer->id) }}" class="form-horizontal">
    @csrf
    @method('PATCH')
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="{{ old('username', $scorer->username) }}" required maxlength="40">
        @error('username')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $scorer->email) }}" required>
        @error('email')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="password">New password</label>
        <input type="password" name="password" id="password" minlength="8" autocomplete="new-password" placeholder="Leave blank to keep current">
        @error('password')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label for="password_confirmation">Confirm new password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" minlength="8" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="mat_number">Mat number</label>
        <input type="number" name="mat_number" id="mat_number" value="{{ old('mat_number', $scorer->mat_number) }}" min="1" max="255" placeholder="Optional">
        @error('mat_number')<span class="error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group">
        <label>
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" value="1" {{ old('active', $scorer->active) === '1' ? 'checked' : '' }}>
            Active
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('manage.scorers.index') }}" class="btn">Cancel</a>
    </div>
</form>
@endsection
