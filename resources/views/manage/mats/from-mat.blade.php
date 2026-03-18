@extends('layouts.autowrestle')

@section('title', 'Move bouts from mat ' . $currentMat . ' – ' . $tournament->TournamentName)
@section('panel_title', 'Move bouts from mat ' . $currentMat)

@section('content')
<p><a href="{{ route('manage.mats.index', $tournament->id) }}">← Back to mat selection</a></p>

@if(session('success'))
    <p class="success">{{ session('success') }}</p>
@endif
@if(session('error'))
    <p class="error">{{ session('error') }}</p>
@endif

@if(empty($bouts))
    <p>No remaining (uncompleted) bouts on mat {{ $currentMat }}. You can mark bouts as completed when scoring; only remaining bouts are shown here for moving.</p>
@elseif(empty($targetMats))
    <p>Mat {{ $currentMat }} is the only configured mat for this tournament. Add more mats in division settings (Start Mat # and Total Mats) to move bouts between mats.</p>
@else
    <p>Select the bouts to move and the target mat. Target mats include those in other divisions.</p>
    <form method="post" action="{{ route('manage.mats.move', $tournament->id) }}" class="form-horizontal">
        @csrf
        <input type="hidden" name="current_mat" value="{{ $currentMat }}">
        <div class="form-group">
            <label>Move to mat</label>
            <select name="target_mat" required>
                <option value="">— Select mat —</option>
                @foreach($targetMats as $m)
                    <option value="{{ $m }}">Mat {{ $m }}</option>
                @endforeach
            </select>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Move</th>
                    <th>Bout #</th>
                    <th>Round</th>
                    <th>Division</th>
                    <th>Wrestler 1</th>
                    <th>Wrestler 2</th>
                    <th>Weight</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bouts as $b)
                    <tr>
                        <td><input type="checkbox" name="bout_ids[]" value="{{ $b->id }}"></td>
                        <td>{{ $b->bout_number ?? $b->id }}</td>
                        <td>{{ $b->round }}</td>
                        <td>{{ $b->division_name }}</td>
                        <td>{{ $b->wr1->wr_first_name }} {{ $b->wr1->wr_last_name }} ({{ $b->wr1->wr_club }})</td>
                        <td>{{ $b->wr2->wr_first_name }} {{ $b->wr2->wr_last_name }} ({{ $b->wr2->wr_club }})</td>
                        <td>{{ $b->weight }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p>
            <button type="submit" class="btn btn-primary">Move selected bouts to chosen mat</button>
            <a href="{{ route('manage.mats.index', $tournament->id) }}" class="btn">Cancel</a>
        </p>
    </form>
@endif
@endsection
