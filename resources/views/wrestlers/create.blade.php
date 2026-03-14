@extends('layouts.autowrestle')

@section('title', 'Add wrestler')

@section('content')
<h1>Add wrestler</h1>
<p><a href="{{ route('wrestlers.index') }}">← My wrestlers</a></p>

<form method="post" action="{{ route('wrestlers.store') }}">
    @csrf
    <p><label for="wr_first_name">First name</label> <input type="text" name="wr_first_name" id="wr_first_name" value="{{ old('wr_first_name') }}" maxlength="30" required></p>
    <p><label for="wr_last_name">Last name</label> <input type="text" name="wr_last_name" id="wr_last_name" value="{{ old('wr_last_name') }}" maxlength="30" required></p>
    <p><label for="wr_club">Club</label> <input type="text" name="wr_club" id="wr_club" value="{{ old('wr_club') }}" maxlength="30" required></p>
    <p><label for="wr_age">Age</label> <input type="number" name="wr_age" id="wr_age" value="{{ old('wr_age') }}" min="3" max="19" required></p>
    <p><label for="wr_grade">Grade</label> <input type="text" name="wr_grade" id="wr_grade" value="{{ old('wr_grade') }}" maxlength="10" required placeholder="e.g. 7 or K"></p>
    <p><label for="wr_weight">Weight</label> <input type="number" name="wr_weight" id="wr_weight" value="{{ old('wr_weight') }}" min="0" max="500" step="0.1"></p>
    <p><label for="wr_years">Years experience</label> <input type="number" name="wr_years" id="wr_years" value="{{ old('wr_years', 0) }}" min="0" max="30" required></p>
    <p><button type="submit">Save</button> <a href="{{ route('wrestlers.index') }}">Cancel</a></p>
</form>
@endsection
