@extends('layouts.autowrestle')

@section('title', 'Log in')
@section('panel_title', 'Login')

@section('content')
<div class="auth-box">
    @if(session('status'))
        <p class="success">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <ul class="error-list">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    @endif
    <form method="post" action="{{ url('/login') }}" class="form-horizontal">
        @csrf
        <div class="form-group">
            <label for="email">Email or username</label>
            <input type="text" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" style="display:block; margin-top:0.35rem; font-size:0.9rem;">Forgot password?</a>
            @endif
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="remember"> Remember Me</label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Login</button>
            @if(Route::has('register'))
                <a href="{{ route('register') }}" style="margin-left:1rem;">Create an account</a>
            @endif
        </div>
    </form>
</div>
@endsection
