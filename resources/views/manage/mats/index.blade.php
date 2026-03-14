@extends('layouts.autowrestle')

@section('title', 'Change mat – ' . $tournament->TournamentName)
@section('panel_title', 'Change mat (move bouts to another mat)')

@section('content')
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>

@if(session('success'))
    <p class="success">{{ session('success') }}</p>
@endif
@if(session('error'))
    <p class="error">{{ session('error') }}</p>
@endif

@if(empty($mats))
    <p>No mats are configured for this tournament. Add divisions with <strong>Start Mat #</strong> and <strong>Total Mats</strong> in <a href="{{ route('manage.divisions.index', $tournament->id) }}">Edit Divisions</a>.</p>
@elseif(empty($matsWithBouts))
    <p>No bouts have been assigned to mats yet. Create bouts from the <strong>Bout</strong> menu first.</p>
@else
    <p>Select the mat you want to move bouts <em>from</em>. Then you can choose which bouts to move and which mat to move them to.</p>
    <form method="get" action="{{ route('manage.mats.fromMat', [$tournament->id, 0]) }}" id="mat-form" class="form-horizontal">
        <div class="form-group">
            <label for="mat">Current mat</label>
            <select name="mat" id="mat" required>
                <option value="">— Select mat —</option>
                @foreach($matsWithBouts as $m)
                    <option value="{{ $m }}">Mat {{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Show bouts on this mat</button>
        </div>
    </form>
    <script>
        document.getElementById('mat-form').addEventListener('submit', function(e) {
            var mat = document.getElementById('mat').value;
            if (!mat) { e.preventDefault(); return; }
            this.action = this.action.replace(/\/from\/0$/, '/from/' + mat);
        });
    </script>
@endif
@endsection
