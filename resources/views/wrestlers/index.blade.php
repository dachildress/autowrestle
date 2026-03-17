@extends('layouts.autowrestle')

@section('title', 'My Wrestlers')
@section('panel_title', 'Manage Wrestlers')

@section('content')
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
<p><a href="{{ route('wrestlers.create') }}" class="btn">Add wrestler</a></p>
@if($wrestlers->isEmpty())
    <p>You have no wrestlers. Add one to get started.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Club</th>
                <th>Age</th>
                <th>Weight</th>
                <th>Grade</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($wrestlers as $w)
            <tr>
                <td>{{ $w->full_name }}</td>
                <td>{{ $w->wr_club }}</td>
                <td>{{ $w->wr_age }}</td>
                <td>{{ $w->wr_weight ?? '–' }}</td>
                <td>{{ $w->wr_grade }}</td>
                <td><a href="{{ route('wrestlers.edit', $w->id) }}" class="btn">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
