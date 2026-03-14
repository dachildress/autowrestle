@extends('layouts.autowrestle')

@section('title', 'Mat settings')
@section('panel_title', 'Mat settings')

@section('content')
@include('mat.nav')

@if($matNumber === null)
    <p class="error">You have no mat assigned.</p>
@else
    <p>Settings for <strong>Mat {{ $matNumber }}</strong>. Display and sound options can be added here.</p>

    <form method="get" action="{{ route('mat.settings') }}" class="form-horizontal" style="max-width: 400px;">
        <div class="form-group">
            <label for="layout">Layout</label>
            <select name="layout" id="layout">
                <option value="">Select a layout</option>
                <option value="default">Default</option>
            </select>
        </div>
        <div class="form-group">
            <label for="font_size">Font size (px)</label>
            <input type="number" name="font_size" id="font_size" value="16" min="12" max="120" style="width: 80px;">
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="sound" value="1"> Sound / horn on event
            </label>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-primary" onclick="alert('Display settings saved (placeholder).');">Display</button>
        </div>
    </form>
@endif
@endsection
