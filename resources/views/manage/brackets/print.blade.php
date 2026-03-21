<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bracket sheets – {{ $division->DivisionName }} – {{ $tournament->TournamentName }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Georgia, 'Times New Roman', serif;
            margin: 0;
            font-size: 11px;
            color: #1a0a0a;
        }
        .no-print { margin: 12px 16px; font-family: system-ui, sans-serif; }
        .no-print a { color: #2563eb; }

        .sheet {
            padding: 0.35in 0.45in;
            page-break-after: always;
            break-after: page;
            max-width: 8.5in;
            margin: 0 auto;
        }
        .sheet:last-child { page-break-after: auto; break-after: auto; }

        .sheet-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .sheet-header h1 {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px;
            text-transform: none;
        }
        .sheet-header .date-line {
            font-size: 12px;
        }

        .meta-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 12px;
            font-family: system-ui, sans-serif;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .meta-box {
            border: 2px solid #7f1d1d;
            padding: 6px 14px;
            min-width: 88px;
            text-align: center;
        }
        .meta-box .label { font-size: 8px; font-weight: 600; margin-top: 4px; color: #444; }

        .round-block { margin-bottom: 14px; }
        .round-title {
            font-family: system-ui, sans-serif;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            padding: 4px 10px;
            border: 2px solid #7f1d1d;
            margin-bottom: 8px;
        }
        .round-block.r1 .round-title { background: #fff; }
        .round-block.r2 .round-title { background: #fef08a; }
        .round-block.r3 .round-title { background: #bbf7d0; }
        .round-block.r4 .round-title { background: #bfdbfe; }
        .round-block.r5 .round-title { background: #e9d5ff; }

        .matches-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
        }
        .match-pair {
            display: flex;
            align-items: stretch;
            gap: 8px;
        }
        .names-stack {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0;
            border: 2px solid #7f1d1d;
        }
        .name-cell {
            padding: 8px 10px;
            min-height: 38px;
            border-bottom: 2px solid #7f1d1d;
            font-weight: 600;
        }
        .name-cell:last-child { border-bottom: none; }
        .name-cell .lbl { font-weight: 800; margin-right: 6px; font-family: system-ui, sans-serif; }

        .bout-num-box {
            border: 2px solid #7f1d1d;
            width: 52px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: system-ui, sans-serif;
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px;
        }
        .bout-num-box .num { font-size: 13px; margin-top: 4px; }

        .bracket-results-label {
            font-family: system-ui, sans-serif;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            margin: 16px 0 6px;
        }
        .bracket-num-inline {
            display: inline-block;
            border: 2px solid #7f1d1d;
            padding: 2px 12px;
            font-weight: 800;
            margin-left: 6px;
        }

        .score-table {
            width: 100%;
            border-collapse: collapse;
            font-family: system-ui, sans-serif;
            font-size: 9px;
        }
        .score-table th, .score-table td {
            border: 2px solid #7f1d1d;
            padding: 5px 6px;
            text-align: center;
        }
        .score-table th { font-weight: 800; text-transform: uppercase; }
        .score-table .col-letter { width: 28px; font-weight: 800; }
        .score-table .col-name { text-align: left; min-width: 120px; }
        .score-table .col-school { text-align: left; min-width: 100px; }
        .score-table .col-score { width: 44px; height: 28px; }

        .note-schedule {
            font-style: italic;
            color: #555;
            margin: 8px 0;
            font-family: system-ui, sans-serif;
            font-size: 10px;
        }

        @page { size: letter portrait; margin: 0.4in; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to tournament</a>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    @php
        $dateStr = $tournament->TournamentDate
            ? $tournament->TournamentDate->format('l F d, Y')
            : '';
    @endphp

    @forelse($sheets as $sheet)
        <div class="sheet">
            <header class="sheet-header">
                <h1>{{ $tournament->TournamentName }}</h1>
                @if($dateStr)
                    <div class="date-line">{{ $dateStr }}</div>
                @endif
            </header>

            <div class="meta-row">
                <div class="meta-box">
                    <div>{{ $sheet['weight_class'] }}</div>
                    <div class="label">Weight class</div>
                </div>
                <div class="meta-box">
                    <div>{{ $division->DivisionName }}</div>
                    <div class="label">Division</div>
                </div>
            </div>

            @if($sheet['schedule_available'] && count($sheet['rounds']) > 0)
                @foreach($sheet['rounds'] as $round)
                    @php $rc = 'r' . min(5, max(1, (int) $round['num'])); @endphp
                    <div class="round-block {{ $rc }}">
                        <div class="round-title">{{ $round['label'] }}</div>
                        <div class="matches-grid">
                            @foreach($round['matches'] as $m)
                                <div class="match-pair">
                                    <div class="names-stack">
                                        <div class="name-cell">
                                            <span class="lbl">{{ $m['a']['letter'] }}</span>
                                            {{ $m['a']['name'] ?? '—' }}
                                        </div>
                                        <div class="name-cell">
                                            <span class="lbl">{{ $m['b']['letter'] }}</span>
                                            {{ $m['b']['name'] ?? '—' }}
                                        </div>
                                    </div>
                                    <div class="bout-num-box">
                                        Match #
                                        <div class="num">{{ $m['bout_display'] ?? '—' }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <p class="note-schedule">
                    @if(! $sheet['schedule_available'])
                        Pairings for this bracket size are not available (supported sizes: 2–6 wrestlers).
                    @else
                        No schedule rows found for this bracket.
                    @endif
                </p>
            @endif

            @php $maxRounds = count($sheet['rounds']); @endphp
            <div class="bracket-results-label">
                Scoring results for bracket # <span class="bracket-num-inline">{{ $sheet['bracket_ordinal'] }}</span>
            </div>

            <table class="score-table">
                <thead>
                    <tr>
                        <th class="col-letter">Letter</th>
                        <th class="col-name">Name</th>
                        <th class="col-school">School</th>
                        @for($r = 1; $r <= max(1, $maxRounds); $r++)
                            <th class="col-score">R{{ $r }}</th>
                        @endfor
                        <th class="col-score">Total</th>
                        <th class="col-score">Place</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sheet['wrestlers'] as $w)
                        <tr>
                            <td class="col-letter">{{ $w['letter'] }}</td>
                            <td class="col-name">{{ $w['name'] }}</td>
                            <td class="col-school">{{ $w['school'] }}</td>
                            @for($r = 1; $r <= max(1, $maxRounds); $r++)
                                <td class="col-score"></td>
                            @endfor
                            <td class="col-score"></td>
                            <td class="col-score"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="sheet">
            <p>No brackets found for this division.</p>
        </div>
    @endforelse
</body>
</html>
