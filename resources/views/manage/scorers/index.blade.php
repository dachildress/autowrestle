@extends('layouts.autowrestle')

@section('title', 'Mat Users')
@section('panel_title', 'Mat Users')

@section('content')
<p><a href="{{ route('manage.tournaments.index') }}">← Back to Manage</a></p>

@if(session('success'))
    <p class="success">{{ session('success') }}</p>
@endif

<p><a href="{{ route('manage.scorers.create') }}" class="btn btn-primary">Add mat user</a></p>

@if($scorers->isEmpty())
    <p>No scorer users yet. Add one to allow mat-side login.</p>
@else
    <table class="table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Mat</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scorers as $s)
                <tr>
                    <td>{{ $s->username }}</td>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->mat_number ?? '—' }}</td>
                    <td>{{ $s->active === '1' ? 'Yes' : 'No' }}</td>
                    <td>
                        <a href="{{ route('manage.scorers.edit', $s->id) }}" class="btn">Edit</a>
                        <form method="post" action="{{ route('manage.scorers.destroy', $s->id) }}" style="display:inline;" onsubmit="return confirm('Remove this mat user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
