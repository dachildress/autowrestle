<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bout sheets – {{ $division->DivisionName }} – {{ $tournament->TournamentName }}</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1rem; font-size: 12px; }
        .bout-sheet { border: 1px solid #333; margin-bottom: 1.5rem; padding: 0.75rem; max-width: 600px; }
        .bout-sheet h3 { margin: 0 0 0.5rem 0; font-size: 14px; }
        .bout-sheet table { width: 100%; border-collapse: collapse; }
        .bout-sheet th, .bout-sheet td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        .bout-sheet th { background: #eee; }
        .pos { font-weight: bold; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <p class="no-print"><a href="{{ route('manage.bouts.selectPrint', $tournament->id) }}">← Back to print selection</a></p>
    <h1>Bout sheets – {{ $division->DivisionName }}</h1>
    <p>{{ $tournament->TournamentName }} — {{ $round ? "Round {$round}" : 'All rounds' }}</p>

    @php $posLabel = function($p) { return ['A','B','C','D','E','F'][$p] ?? (string)$p; }; @endphp
    @foreach($bouts as $b)
    <div class="bout-sheet">
        <table>
            <tr>
                <th>Division</th>
                <td>{{ $division->DivisionName }}</td>
                <th>Weight</th>
                <td>{{ $b->weight }}</td>
                <th>Round</th>
                <td>{{ $b->round }}</td>
                <th>Match</th>
                <td>{{ $b->bout_number ?? $b->id }}</td>
                <th>Mat</th>
                <td>{{ $b->mat_number }}</td>
            </tr>
            <tr>
                <th class="pos">{{ $posLabel($b->wr1->wr_pos) }}</th>
                <td colspan="3">{{ $b->wr1->wr_first_name }} {{ $b->wr1->wr_last_name }} — {{ $b->wr1->wr_club }}</td>
                <th>1st</th><td></td><th>2nd</th><td></td><th>3rd</th><td></td>
            </tr>
            <tr>
                <th class="pos">{{ $posLabel($b->wr2->wr_pos) }}</th>
                <td colspan="3">{{ $b->wr2->wr_first_name }} {{ $b->wr2->wr_last_name }} — {{ $b->wr2->wr_club }}</td>
                <td colspan="6"></td>
            </tr>
            <tr>
                <td colspan="10" style="padding: 6px;">Referee: _______________ &nbsp; Scorer: _______________ &nbsp; Winner: _______________</td>
            </tr>
        </table>
    </div>
    @endforeach

    @if(empty($bouts))
        <p>No bouts to print.</p>
    @endif
</body>
</html>
