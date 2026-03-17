@extends('layouts.autowrestle')

@section('title', 'Register')
@section('panel_title', 'Register')

@section('content')
<div class="auth-box">
    @if($errors->any())
        <ul class="error-list">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    @endif
    <form method="post" action="{{ url('/register') }}" class="form-horizontal">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="form-group">
            <label for="email">E-Mail Address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>
    <p><a href="{{ route('login') }}">Already have an account? Login</a></p>
</div>
@endsection
