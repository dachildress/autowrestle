<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bout sheets – {{ $division->DivisionName }} – {{ $tournament->TournamentName }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; font-size: 10px; color: #0f172a; text-transform: uppercase; }

        .bord { border: 1px solid #9a3412; }
        .top-row { display: flex; gap: 6px; margin-bottom: 6px; }
        .top-box { border: 1px solid #9a3412; padding: 4px 8px; font-weight: 700; flex: 1; }
        .top-box:first-of-type { flex: 1.4; }
        .top-box:nth-of-type(2) { flex: 1; }
        .top-box:nth-of-type(3) { flex: 0.5; }
        .top-box:nth-of-type(4) { flex: 0.45; }
        .top-box:nth-of-type(5) { flex: 0.45; }

        .sheet-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .sheet-table th, .sheet-table td { border: 1px solid #9a3412; padding: 2px 4px; vertical-align: middle; }
        .sheet-table th { font-weight: 700; font-size: 9px; text-align: center; }
        .pos-cell { width: 28px; text-align: center; font-weight: 800; font-size: 12px; }
        .name-cell { width: 18%; }
        .wrestler-name { font-weight: 700; line-height: 1.2; }
        .wrestler-club { font-size: 9px; font-weight: 600; line-height: 1.1; }
        .score-cell { min-height: 36px; height: 36px; } /* 1ST, 2ND, 3RD */
        .udnf-cell { width: 22px; padding: 2px; font-size: 8px; font-weight: 700; text-align: center; line-height: 1.3; }
        .ot-cell { width: 26px; min-height: 36px; }
        .total-cell { width: 44px; }
        .legend-row { margin-top: 6px; border: 1px solid #9a3412; padding: 4px 6px; font-size: 8px; text-transform: uppercase; }
        .footer-row { margin-top: 6px; }
        .footer-row table { width: 100%; border-collapse: collapse; }
        .footer-row td { border: 1px solid #9a3412; padding: 4px 6px; font-weight: 700; }
        .footer-row .winner-cell { width: 50%; }

        .bout-sheet { padding: 8px; page-break-inside: avoid; break-inside: avoid; }

        /* Two sheets per page: one at top, one at bottom (stacked vertically) */
        .print-page {
            page-break-after: always;
            break-after: page;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 0.2in;
        }
        /* At least 1 inch between the two bout sheets on the page */
        .print-page .bout-sheet:first-child:not(:only-child) {
            margin-bottom: 1in;
        }
        .print-page:last-child { page-break-after: auto; }

        @page { size: letter landscape; margin: 0.25in; }
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .print-page { min-height: 50vh; }
        }
    </style>
</head>
<body>
    <p class="no-print"><a href="{{ route('manage.bouts.selectPrint', $tournament->id) }}">← Back to print selection</a></p>
    <h1 class="no-print">Bout sheets – {{ $division->DivisionName }}</h1>
    <p class="no-print">{{ $tournament->TournamentName }} — {{ $round ? "Round {$round}" : 'All rounds' }}</p>
    <p class="no-print text-slate-600" style="font-size:11px;">
        Order: round → mat → bout #.
        @if(!empty($mats_on_sheets))
            <strong>On this print:</strong> mats {{ implode(', ', $mats_on_sheets) }} ({{ count($bouts) }} bout{{ count($bouts) !== 1 ? 's' : '' }}).
        @endif
        @if(!empty($print_mats))
            <span class="text-slate-500">Reference mat list (scheme + division + DB): {{ implode(', ', $print_mats) }}.</span>
        @endif
    </p>

    @php $posLabel = function($p) { return ['A','B','C','D','E','F'][$p] ?? (string)$p; }; @endphp
    @php $boutChunks = is_array($bouts) ? array_chunk($bouts, 2) : $bouts->chunk(2)->values()->all(); @endphp
    @if(!empty($bouts))
    @foreach($boutChunks as $chunk)
    <div class="print-page">
        @foreach($chunk as $b)
        <div class="bout-sheet">
            <div class="top-row">
                <div class="top-box bord">DIVISION: {{ $division->DivisionName }}</div>
                <div class="top-box bord">WEIGHT: {{ $b->weight }}</div>
                <div class="top-box bord">ROUND: {{ $b->round }}</div>
                <div class="top-box bord">MAT: {{ $b->mat_number ?? '—' }}</div>
                <div class="top-box bord">Match: {{ $b->bout_number ?? $b->id }}</div>
            </div>

            <table class="sheet-table">
                <thead>
                    <tr>
                        <th style="width:28px"></th>
                        <th style="width:18%"></th>
                        <th>1ST</th>
                        <th style="width:22px"></th>
                        <th>2ND</th>
                        <th style="width:22px"></th>
                        <th>3RD</th>
                        <th style="width:26px">1:00 OT</th>
                        <th style="width:22px"></th>
                        <th style="width:26px">:30 TB</th>
                        <th style="width:22px"></th>
                        <th style="width:26px">:30 TB</th>
                        <th style="width:26px">:30 UTB</th>
                        <th style="width:44px">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="pos-cell bord">{{ $posLabel($b->wr1->wr_pos) }}</td>
                        <td class="name-cell bord">
                            <div class="wrestler-name">{{ $b->wr1->wr_first_name }} {{ $b->wr1->wr_last_name }}</div>
                            <div class="wrestler-club">{{ $b->wr1->wr_club }}</div>
                        </td>
                        <td class="score-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="score-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="score-cell bord"></td>
                        <td class="ot-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="ot-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="ot-cell bord"></td>
                        <td class="ot-cell bord"></td>
                        <td class="total-cell bord"></td>
                    </tr>
                    <tr>
                        <td class="pos-cell bord">{{ $posLabel($b->wr2->wr_pos) }}</td>
                        <td class="name-cell bord">
                            <div class="wrestler-name">{{ $b->wr2->wr_first_name }} {{ $b->wr2->wr_last_name }}</div>
                            <div class="wrestler-club">{{ $b->wr2->wr_club }}</div>
                        </td>
                        <td class="score-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="score-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="score-cell bord"></td>
                        <td class="ot-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="ot-cell bord"></td>
                        <td class="udnf-cell bord">UP<br>DN<br>NU<br>DF</td>
                        <td class="ot-cell bord"></td>
                        <td class="ot-cell bord"></td>
                        <td class="total-cell bord"></td>
                    </tr>
                </tbody>
            </table>

            <div class="footer-row">
                <table>
                    <tr>
                        <td style="width:25%" class="bord">REFEREE:</td>
                        <td style="width:25%" class="bord">SCORER:</td>
                        <td class="winner-cell bord">WINNER:</td>
                    </tr>
                </table>
            </div>

            <div class="legend-row bord">
                T3 - TAKEDOWN | R2 - REVERSAL | E1 - ESCAPE | NF2 - NEAR FALL | NF4 - NEAR FALL (FOUR SECONDS) | TV - TECH VIOLATION | NF5 - INJ NEAR FALL / BLEEDING | SW - STALLING WARNING | S - STALLING | P - ILLEGAL HOLD / UNNECESSARY ROUGHNESS | C - CAUTION | C1 - POINT EARNED AFTER 2ND CAUTION
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
    @endif

    @if(empty($bouts))
        <p>No bouts to print.</p>
    @endif
</body>
</html>
