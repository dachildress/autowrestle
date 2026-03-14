@extends('layouts.autowrestle')

@section('title', 'Brackets – ' . $group->Name . ' – ' . $tournament->TournamentName)

@push('styles')
<style>
.bracket-drop-zone { min-height: 1.5em; }
.bracket-options { white-space: nowrap; }
.bracket-options a, .bracket-options form { display: inline-block; margin: 0 2px; }
.bracket-options .move-select { max-width: 4em; padding: 2px; font-size: 0.9rem; }
.wrestler-name-click { background: none; border: none; padding: 0; font: inherit; color: inherit; cursor: pointer; text-align: left; text-decoration: none; }
.wrestler-name-click:hover { color: #18bc9c; text-decoration: underline; }
.tDnD_whileDrag { opacity: 0.6; }
.bracket-drag-handle { cursor: move; user-select: none; }
</style>
@endpush

@section('content')
<h1>Brackets – {{ $division->DivisionName }}</h1>
<p><a href="{{ route('manage.tournaments.show', $tournament->id) }}">← Back to {{ $tournament->TournamentName }}</a></p>

<ul class="group-tabs">
    @foreach($groupsInDivision as $g)
        <li class="{{ $g->id == $group->id ? 'active' : '' }}">
            @if($g->id == $group->id)
                <strong>{{ $g->Name }}</strong>
            @else
                <a href="{{ route('manage.brackets.show', [$tournament->id, $g->id]) }}">{{ $g->Name }}</a>
            @endif
        </li>
    @endforeach
</ul>

@if($bouted)
    <p><strong>Brackets are locked.</strong> To make changes you must un-bout this division.</p>
@endif

@if(!$bouted)
<form id="bracket-drop-move-form" method="post" action="" style="display:none;">
    @csrf
    <input type="hidden" name="target_bracket_id" id="bracket-drop-target-id" value="">
</form>
@endif
<table id="brackets-table" data-move-url-base="{{ url('tournaments/manage/'.$tournament->id.'/movewrestler') }}" data-tid="{{ $tournament->id }}">
    <thead>
        <tr class="nodrop nodrag">
            <th>Bracket</th>
            <th>Name</th>
            <th>Club</th>
            <th style="text-align: center;">Age</th>
            <th style="text-align: center;">Grade</th>
            <th style="text-align: center;">Weight</th>
            <th style="text-align: center;">Years</th>
            <th style="text-align: center;">Pos</th>
            @if(!$bouted)
                <th>Options</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php $prevBracket = null; $bracketIds = $bracketCounts->keys(); @endphp
        @foreach($wrestlers as $w)
            @if($prevBracket !== null && $prevBracket != $w->bracket_id)
                <tr class="nodrop nodrag" id="sep-{{ $w->bracket_id }}">
                    <td colspan="{{ $bouted ? 9 : 10 }}" style="border-top: 2px solid #999;">—</td>
                </tr>
            @endif
            @php $prevBracket = $w->bracket_id; @endphp
            <tr class="bracket-row" id="{{ $w->id }}" data-wid="{{ $w->id }}" data-bracket-id="{{ $w->bracket_id }}">
                @php $count = $bracketCounts[$w->bracket_id] ?? 0; @endphp
                <td class="bracket-drop-zone {{ !$bouted ? 'bracket-drag-handle' : '' }}" data-bracket-id="{{ $w->bracket_id }}" @if($count < $perBracket) style="background: #ffc;" @elseif($count > $perBracket) style="background: #6cf;" @endif>
                    {{ $w->bracket_id }}
                </td>
                <td @if(str_starts_with((string)$w->wr_first_name, '*')) style="background: #deb887;" @endif>
                    @if(!$bouted)
                        <form method="post" action="{{ route('manage.brackets.moveWrestlerToLast', [$tournament->id, $w->id]) }}" style="display:inline;" class="name-move-form">
                            @csrf
                            <button type="submit" class="wrestler-name-click" title="Move to last position in this bracket">{{ $w->wr_first_name }} {{ $w->wr_last_name }}</button>
                        </form>
                    @else
                        {{ $w->wr_first_name }} {{ $w->wr_last_name }}
                    @endif
                </td>
                <td>{{ $w->wr_club }}</td>
                <td style="text-align: center;">{{ $w->wr_age }}</td>
                <td style="text-align: center;">{{ $w->wr_grade }}</td>
                <td style="text-align: center;">{{ $w->wr_weight }}</td>
                <td style="text-align: center;">{{ $w->wr_years }}</td>
                <td style="text-align: center;">{{ $w->wr_pos }}</td>
                @if(!$bouted)
                    <td class="bracket-options">
                        <a href="{{ route('manage.brackets.moveWrestlerForm', [$tournament->id, $w->id]) }}?return={{ urlencode(route('manage.brackets.show', [$tournament->id, $group->id])) }}" title="Move wrestler to another group" aria-label="Move to group">→</a>
                        &nbsp;<a href="{{ route('manage.viewgroups.editWrestler', [$tournament->id, $w->id]) }}?return={{ urlencode(route('manage.brackets.show', [$tournament->id, $group->id])) }}" title="Edit wrestler" aria-label="Edit">✎</a>
                        &nbsp;<a href="{{ route('manage.brackets.deleteWrestler', [$tournament->id, $w->id]) }}" onclick="return confirm('Remove this wrestler from brackets and tournament?');" title="Delete from tournament" aria-label="Delete">×</a>
                    </td>
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

@if($wrestlers->isEmpty())
    <p>No wrestlers in brackets for this group.</p>
@endif

<p>
    <a href="{{ route('manage.tournaments.show', $tournament->id) }}">Back to tournament</a>
    | <a href="{{ route('manage.brackets.unbracket', [$tournament->id, $division->id]) }}" onclick="return confirm('Clear all brackets and bouts for {{ $division->DivisionName }}?');">Unbracket this division</a>
</p>

@if(!$bouted && $wrestlers->isNotEmpty())
<style>.tDnD_whileDrag { opacity: 0.6; } .bracket-drag-handle { cursor: move; }</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/1.0.5/jquery.tablednd.min.js"></script>
<script>
(function() {
    var moveUrlBase = document.getElementById('brackets-table').getAttribute('data-move-url-base');
    var dropForm = document.getElementById('bracket-drop-move-form');
    var targetInput = document.getElementById('bracket-drop-target-id');
    if (!moveUrlBase || !dropForm || !targetInput) return;

    jQuery(function($) {
        $('#brackets-table').tableDnD({
            onDragClass: 'tDnD_whileDrag',
            onDrop: function(table, row) {
                var wid = row.id;
                if (!wid || String(wid).indexOf('sep') === 0) return;
                var tbody = table.tBodies[0];
                if (!tbody) return;
                var rows = tbody.rows;
                var idx = -1;
                for (var i = 0; i < rows.length; i++) {
                    if (String(rows[i].id) === String(wid)) { idx = i; break; }
                }
                if (idx < 0) return;
                var targetBracketId = null;
                var prevRow = idx > 0 ? rows[idx - 1] : null;
                var nextRow = idx < rows.length - 1 ? rows[idx + 1] : null;
                var prevCell = prevRow && prevRow.cells[0] ? prevRow.cells[0] : null;
                var nextCell = nextRow && nextRow.cells[0] ? nextRow.cells[0] : null;
                if (prevCell && prevCell.getAttribute('data-bracket-id')) targetBracketId = prevCell.getAttribute('data-bracket-id');
                if (!targetBracketId && nextCell && nextCell.getAttribute('data-bracket-id')) targetBracketId = nextCell.getAttribute('data-bracket-id');
                if (!targetBracketId) return;
                var currentBracket = row.getAttribute('data-bracket-id');
                if (targetBracketId === currentBracket) return;
                try {
                    sessionStorage.setItem('bracketScrollX', String(window.scrollX));
                    sessionStorage.setItem('bracketScrollY', String(window.scrollY));
                } catch (e) {}
                dropForm.action = moveUrlBase + '/' + wid;
                targetInput.value = targetBracketId;
                dropForm.submit();
            }
        });
    });
})();
</script>
@endif
<script>
(function() {
    try {
        var x = sessionStorage.getItem('bracketScrollX');
        var y = sessionStorage.getItem('bracketScrollY');
        if (y !== null) {
            sessionStorage.removeItem('bracketScrollX');
            sessionStorage.removeItem('bracketScrollY');
            var scrollX = x !== null ? parseInt(x, 10) : 0;
            var scrollY = parseInt(y, 10);
            function restore() {
                window.scrollTo(scrollX, scrollY);
            }
            if (typeof requestAnimationFrame !== 'undefined') {
                requestAnimationFrame(restore);
            } else {
                restore();
            }
        }
    } catch (e) {}
})();
</script>
@endsection
