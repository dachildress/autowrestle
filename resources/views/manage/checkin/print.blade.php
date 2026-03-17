<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Check-in – {{ $division->DivisionName }} – {{ $tournament->TournamentName }}</title>
    <style>
        table { border-collapse: collapse; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px 8px; }
        .no-print { margin-bottom: 1rem; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <p class="no-print"><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back</a></p>
    <p class="no-print"><button onclick="window.print()">Print this page</button></p>

    <h2>{{ $division->DivisionName }} — {{ $count }} wrestlers</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Club</th>
                <th>Age</th>
                <th>Grade</th>
                <th>Weight</th>
                <th>Years</th>
                <th>Act WGT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wrestlers as $w)
            <tr>
                <td>{{ $w->wr_first_name }} {{ $w->wr_last_name }}</td>
                <td>{{ $w->wr_club }}</td>
                <td style="text-align: center;">{{ $w->wr_age }}</td>
                <td style="text-align: center;">{{ $w->wr_grade }}</td>
                <td style="text-align: center;">{{ $w->wr_weight }}</td>
                <td style="text-align: center;">{{ $w->wr_years }}</td>
                <td style="text-align: center;"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
