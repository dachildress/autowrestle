@extends('layouts.autowrestle')

@section('title', $division->DivisionName . ' – ' . $tournament->TournamentName)

@section('content')
<div style="text-align: center; margin-bottom: 1rem;">
    <h2 style="margin: 0;">{{ $tournament->TournamentName }} {{ $tournament->TournamentDate->format('m-d-Y') }}</h2>
</div>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if(session('error'))<p class="error">{{ session('error') }}</p>@endif
<p>
    <a href="{{ route('manage.divisions.index', $tournament->id) }}">&larr; Back Divisions</a>
    &nbsp;|&nbsp;
    <a href="{{ route('manage.divisions.period-settings.index', [$tournament->id, $division->id]) }}">Period timing</a>
</p>

{{-- Editable division: same page lets you edit division and manage groups --}}
<form method="post" action="{{ route('manage.divisions.update', [$tournament->id, $division->id]) }}" id="division-form">
    @csrf
</form>
<table class="table-dark">
    <thead>
        <tr>
            <th>Name</th>
            <th style="text-align: center;">Start-Mat#</th>
            <th style="text-align: center;">Total-Mats</th>
            <th style="text-align: center;">Wrestlers Per Bracket</th>
            <th style="text-align: center;">Edit</th>
            <th style="text-align: center;">Delete</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input type="text" name="DivisionName" form="division-form" value="{{ old('DivisionName', $division->DivisionName) }}" maxlength="45" required style="width:100%; box-sizing:border-box;"></td>
            <td style="text-align: center;"><input type="number" name="StartingMat" form="division-form" value="{{ old('StartingMat', $division->StartingMat) }}" min="0" required style="width:4em;"></td>
            <td style="text-align: center;"><input type="number" name="TotalMats" form="division-form" value="{{ old('TotalMats', $division->TotalMats) }}" min="0" required style="width:4em;"></td>
            <td style="text-align: center;"><input type="number" name="PerBracket" form="division-form" value="{{ old('PerBracket', $division->PerBracket) }}" min="0" required style="width:4em;"></td>
            <td style="text-align: center;"><button type="submit" form="division-form" class="btn">Edit</button></td>
            <td style="text-align: center;">
                <a href="{{ route('manage.divisions.destroy', [$tournament->id, $division->id]) }}" class="btn" onclick="return confirm('Delete this division? All groups and wrestlers in it will be deleted.');">Delete</a>
            </td>
        </tr>
    </tbody>
</table>
@if($errors->any())
    <ul class="error-list">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
@endif

<h2>{{ $division->DivisionName }} Groups</h2>
<p><a href="{{ route('manage.groups.create', [$tournament->id, $division->id]) }}" class="btn btn-info">Add New Group</a></p>

@if($division->divGroups->isEmpty())
    <p>No groups.</p>
@else
    <table class="table-dark">
        <thead>
            <tr>
                <th>Name</th>
                <th style="text-align: center;">Minimum Age</th>
                <th style="text-align: center;">Max Age</th>
                <th style="text-align: center;">Minimum Grade</th>
                <th style="text-align: center;">Max Grade</th>
                <th style="text-align: center;">Max Weight Diff</th>
                <th style="text-align: center;">Max Power Diff</th>
                <th style="text-align: center;">Max Experience Diff</th>
                <th style="text-align: center;">Options</th>
            </tr>
        </thead>
        <tbody>
            @foreach($division->divGroups as $group)
            <tr>
                <td>{{ $group->Name }}</td>
                <td style="text-align: center;">{{ $group->MinAge }}</td>
                <td style="text-align: center;">{{ $group->MaxAge }}</td>
                <td style="text-align: center;">{{ $group->MinGrade }}</td>
                <td style="text-align: center;">{{ $group->MaxGrade }}</td>
                <td style="text-align: center;">{{ $group->MaxWeightDiff }}</td>
                <td style="text-align: center;">{{ $group->MaxPwrDiff }}</td>
                <td style="text-align: center;">{{ $group->MaxExpDiff }}</td>
                <td style="text-align: center;">
                    <a href="{{ route('manage.groups.edit', [$tournament->id, $division->id, $group->id]) }}" class="btn">Edit</a>
                    <a href="{{ route('manage.groups.destroy', [$tournament->id, $division->id, $group->id]) }}" class="btn" onclick="return confirm('Delete this group?');">Delete</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
