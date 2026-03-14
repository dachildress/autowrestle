@extends('layouts.autowrestle')

@section('title', 'Coming up / Projection – ' . $tournament->TournamentName)
@section('panel_title', 'Coming up / Projection views')

@section('content')
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>

@if(session('success'))
    <p class="success">{{ session('success') }}</p>
@endif

<p>Configure views to show which wrestlers are coming up per mat (e.g. one view per gym). Each view can show selected groups and a set number of bouts per mat. Open <strong>Display</strong> to project on the wall; it auto-refreshes every 10 seconds.</p>

<p><a href="{{ route('manage.projection.create', $tournament->id) }}" class="btn btn-primary">Add projection view</a></p>

@if($views->isEmpty())
    <p>No projection views yet. Add one to get started.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Bouts per mat</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($views as $v)
                <tr>
                    <td>{{ $v->name }}</td>
                    <td>{{ $v->wrestlers_per_mat }}</td>
                    <td>
                        <a href="{{ route('manage.projection.display', [$tournament->id, $v->id]) }}" class="btn btn-success" target="_blank">Display</a>
                        <a href="{{ route('manage.projection.edit', [$tournament->id, $v->id]) }}" class="btn">Edit</a>
                        <form method="post" action="{{ route('manage.projection.destroy', [$tournament->id, $v->id]) }}" style="display:inline;" onsubmit="return confirm('Delete this projection view?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
