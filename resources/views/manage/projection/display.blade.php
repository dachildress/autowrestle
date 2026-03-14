<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coming up – {{ $view->name }} – {{ $tournament->TournamentName }}</title>
    <meta http-equiv="refresh" content="10">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Lato', sans-serif; background: #1a1a1a; color: #fff; padding: 12px; min-height: 100vh; }
        .header { text-align: center; margin-bottom: 12px; font-size: 1.4rem; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { padding: 8px 10px; text-align: left; border: 1px solid #444; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        th { background: #3498db; color: #fff; font-weight: 700; }
        tr.current { background: #27ae60; color: #fff; }
        tr.next { background: #f1c40f; color: #1a1a1a; }
        tr.upcoming { background: #3d3d3d; }
        tr.empty-row { color: #666; }
        tr.empty-row td { font-style: italic; }
        .refresh-note { text-align: center; margin-top: 10px; font-size: 0.85rem; color: #666; }
        /* Column widths: Mat# narrow, Bout# narrow, then flexible */
        th:nth-child(1), td:nth-child(1) { width: 4%; min-width: 48px; }
        th:nth-child(2), td:nth-child(2) { width: 6%; min-width: 72px; }
        th:nth-child(3), td:nth-child(3) { width: 18%; }
        th:nth-child(4), td:nth-child(4) { width: 12%; }
        th:nth-child(5), td:nth-child(5) { width: 18%; }
        th:nth-child(6), td:nth-child(6) { width: 12%; }
        th:nth-child(7), td:nth-child(7) { width: 10%; }
        th:nth-child(8), td:nth-child(8) { width: 12%; }
    </style>
</head>
<body>
    <div class="header">{{ $tournament->TournamentName }} – {{ $view->name }} (refreshes every 10s)</div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mat#</th>
                    <th>Bout#</th>
                    <th>Wrestler 1</th>
                    <th>Wrestler 1 team</th>
                    <th>Wrestler 2</th>
                    <th>Wrestler 2 team</th>
                    <th>Weight</th>
                    <th>Group</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matsData as $mat => $rows)
                    @foreach($rows as $r)
                        @if($r === null)
                            <tr class="empty-row">
                                <td>{{ $mat }}</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                            </tr>
                        @else
                            <tr class="{{ $r->row_type }}">
                                <td>{{ $mat }}</td>
                                <td>{{ $r->bout->id }}</td>
                                <td>{{ $r->bout->wr1->wr_first_name }} {{ $r->bout->wr1->wr_last_name }}</td>
                                <td>{{ $r->bout->wr1->wr_club }}</td>
                                <td>{{ $r->bout->wr2->wr_first_name }} {{ $r->bout->wr2->wr_last_name }}</td>
                                <td>{{ $r->bout->wr2->wr_club }}</td>
                                <td>{{ $r->bout->weight }}</td>
                                <td>{{ $r->bout->group }}</td>
                            </tr>
                        @endif
                    @endforeach
                @empty
                    <tr><td colspan="8" style="text-align:center; color: #888;">No mats for the selected groups. Adjust the view’s groups or division mat settings.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="refresh-note">Auto-refresh every 10 seconds</p>
</body>
</html>
