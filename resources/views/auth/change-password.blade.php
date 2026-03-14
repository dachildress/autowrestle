@extends('layouts.autowrestle')

@section('title', 'Change password')
@section('panel_title', 'Change password')

@section('content')
<div class="auth-box">
    @if(session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif
    @if($errors->any())
        <ul class="error-list">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    @endif
    <form method="post" action="{{ route('password.change.update') }}" class="form-horizontal">
        @csrf
        @method('put')
        <div class="form-group">
            <label for="current_password">Current password</label>
            <input type="password" name="current_password" id="current_password" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label for="password">New password</label>
            <input type="password" name="password" id="password" required autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm new password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update password</button>
            <a href="{{ url()->previous() }}" style="margin-left:1rem;">Cancel</a>
        </div>
    </form>
</div>
@endsection
