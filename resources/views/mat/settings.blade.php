@extends('layouts.mat')

@section('title', 'Mat settings')
@section('panel_title', 'Mat settings')

@section('content')
@include('mat.nav')

@if($matNumber === null)
    <p class="error">You have no mat assigned.</p>
@else
    @if(session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif
    <p>Settings for <strong>Mat {{ $matNumber }}</strong>. Choose which timers appear on the audience scoreboard when you open Virtual and click Display.</p>

    <form method="post" action="{{ route('mat.settings.store') }}" class="form-horizontal" style="max-width: 400px;">
        @csrf
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="show_head_neck" id="show-head-neck" value="1" {{ ($showHeadNeck ?? false) ? 'checked' : '' }}>
                <strong>Show Head/Neck timer</strong>
            </label>
        </div>
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="show_recover" id="show-recover" value="1" {{ ($showRecover ?? false) ? 'checked' : '' }}>
                <strong>Show Recovery timer</strong>
            </label>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ $currentBoutId ? route('mat.bout.show', ['boutId' => $currentBoutId]) : route('mat.dashboard') }}" class="btn">Cancel</a>
        </div>
    </form>
@endif
@endsection
