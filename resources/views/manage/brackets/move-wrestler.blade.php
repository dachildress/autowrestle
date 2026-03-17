@extends('layouts.autowrestle')

@section('title', 'Move Wrestler – ' . $tournament->TournamentName)
@section('panel_title', 'Move Wrestler')

@section('content')
@if(session('error'))<p class="error">{{ session('error') }}</p>@endif
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if(session('info'))<p class="success">{{ session('info') }}</p>@endif

<table class="table" style="margin-bottom: 1.5rem;">
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Age</th>
            <th>Grade</th>
            <th>Weight</th>
            <th>Years</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $wrestler->wr_first_name }}</td>
            <td>{{ $wrestler->wr_last_name }}</td>
            <td>{{ $wrestler->wr_age }}</td>
            <td>{{ $wrestler->wr_grade }}</td>
            <td>{{ $wrestler->wr_weight }}</td>
            <td>{{ $wrestler->wr_years }}</td>
        </tr>
    </tbody>
</table>

<form method="post" action="{{ route('manage.brackets.moveWrestlerToGroup', [$tournament->id, $wrestler->id]) }}">
    @csrf
    @if(!empty($returnUrl))<input type="hidden" name="return" value="{{ $returnUrl }}">@endif
    <div class="form-group" style="margin-bottom: 1rem;">
        <label for="group_id" style="min-width: 80px;">Group</label>
        <select name="group_id" id="group_id" required style="padding: 6px 10px; min-width: 200px;">
            <option value="">Select Group</option>
            @foreach($groups as $g)
                @php $optionValue = $g->Division_id . '_' . $g->id; @endphp
                <option value="{{ $optionValue }}" {{ old('group_id') == $optionValue ? 'selected' : '' }}>{{ $g->division_name ?? '' }}{{ $g->division_name ? ': ' : '' }}{{ $g->display_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-actions" style="margin-top: 1rem;">
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ $returnUrl }}" class="btn btn-danger">Back</a>
    </div>
</form>
@endsection
