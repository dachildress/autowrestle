@extends('layouts.mat')

@section('title', 'Bout ' . $boutId . ' – Mat-side scoring')

@php
    $fmtTime = fn ($sec) => sprintf('%d:%02d', (int)($sec / 60), (int)($sec % 60));
@endphp

@section('content')
<style>
    .mat-scoring-page { display: flex; min-height: 60vh; }
    .mat-scoring-nav { width: 180px; flex-shrink: 0; background: #f5f5f5; border-right: 1px solid #ddd; padding: 1rem 0; }
    .mat-scoring-nav a { display: block; padding: 0.5rem 1rem; color: #2c3e50; text-decoration: none; }
    .mat-scoring-nav a:hover { background: #e0e0e0; }
    .mat-scoring-main { flex: 1; min-width: 0; padding: 0 1rem; }
    .mat-scoring { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
    .mat-scoring .col-red { flex: 1; min-width: 240px; background: #fff; border: 2px solid #c00; border-radius: 4px; padding: 1rem; }
    .mat-scoring .col-center { flex: 1; min-width: 220px; background: #333; color: #fff; border-radius: 8px; padding: 1rem; }
    .mat-scoring .col-green { flex: 1; min-width: 240px; background: #fff; border: 2px solid #080; border-radius: 4px; padding: 1rem; }
    .mat-scoring .wrestler-name { font-weight: 700; font-size: 1.1rem; text-decoration: underline; }
    .mat-scoring .col-red.side-display-red .wrestler-name,
    .mat-scoring .col-red.side-display-red .score-label { color: #c00; }
    .mat-scoring .col-red.side-display-green .wrestler-name,
    .mat-scoring .col-red.side-display-green .score-label { color: #080; }
    .mat-scoring .col-green.side-display-green .wrestler-name,
    .mat-scoring .col-green.side-display-green .score-label { color: #080; }
    .mat-scoring .col-green.side-display-red .wrestler-name,
    .mat-scoring .col-green.side-display-red .score-label { color: #c00; }
    .mat-scoring .score-header-row { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center; }
    .mat-scoring .score-header-row select { padding: 0.25rem 0.5rem; }
    .mat-scoring .big-score { font-size: 2rem; font-weight: 700; text-align: center; margin: 0.5rem 0; }
    .mat-scoring .col-center .clock-display { font-size: 2rem; font-weight: 700; color: #f66; text-align: center; margin: 0.5rem 0; }
    .mat-score-grid { display: grid; grid-template-columns: auto 1fr auto; gap: 0.5rem 1rem; align-items: center; margin: 0.75rem 0; }
    .mat-score-grid .btn-num { min-width: 36px; padding: 6px 8px; cursor: pointer; background: #ddd; border: 1px solid #999; border-radius: 2px; font-weight: 600; }
    .mat-score-grid .score-label { text-decoration: underline; font-size: 0.95rem; }
    .mat-score-grid .score-label span.pts { text-decoration: none; color: #333; }
    .mat-scoring .timer-block { margin: 0.75rem 0; padding: 0.75rem; border: 2px solid #c00; border-radius: 4px; background: #fff; }
    .mat-scoring .col-green .timer-block { border-color: #080; }
    .mat-scoring .timer-block .label { font-size: 0.9rem; font-weight: 600; margin-bottom: 0.25rem; }
    .mat-scoring .timer-block .display { font-family: monospace; font-size: 1.35rem; margin: 0.25rem 0; }
    .mat-scoring .timer-block .timer-btns { display: flex; gap: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap; }
    .mat-events { margin-top: 1.5rem; }
    .mat-events h3 { margin-bottom: 0.5rem; }
    .mat-events ul { list-style: none; padding: 0; margin: 0; max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 0.5rem; }
    .mat-events li { padding: 0.25rem 0; border-bottom: 1px solid #eee; font-size: 0.9rem; }
    .mat-events li .side-red { color: #c00; }
    .mat-events li .side-green { color: #080; }
</style>

@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if(session('error'))<p class="error">{{ session('error') }}</p>@endif

<div class="mat-scoring-page">
    <nav class="mat-scoring-nav">
        <a href="{{ route('mat.dashboard') }}">Match list</a>
        <a href="{{ route('mat.bout.show', ['boutId' => $boutId]) }}">Scoring</a>
        <a href="{{ route('mat.bout.history', ['boutId' => $boutId]) }}">Summary</a>
        <a href="{{ route('mat.bout.results', ['boutId' => $boutId]) }}">Results</a>
        <a href="{{ route('mat.virtual') }}" class="mat-virtual-link" data-virtual-url="{{ route('mat.virtual') }}">Virtual</a>
        <a href="{{ route('mat.settings') }}">Settings</a>
        <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('mat-logout-form').submit();">Logout</a>
        <form id="mat-logout-form" action="{{ url('/logout') }}" method="post" style="display: none;">@csrf</form>
    </nav>
    <div class="mat-scoring-main">
        <p style="margin: 0 0 0.5rem;"><strong>{{ $divisionName }}</strong> — Bout {{ $boutId }}</p>

<div class="mat-scoring" id="mat-scoring"
    data-csrf="{{ csrf_token() }}"
    data-url-state="{{ route('mat.bout.state', ['boutId' => $boutId]) }}"
    data-url-clock="{{ route('mat.bout.clock', ['boutId' => $boutId]) }}"
    data-url-period="{{ route('mat.bout.period', ['boutId' => $boutId]) }}"
    data-url-event="{{ route('mat.bout.event', ['boutId' => $boutId]) }}"
    data-url-comment="{{ route('mat.bout.comment', ['boutId' => $boutId]) }}"
    data-url-timer="{{ route('mat.bout.timer', ['boutId' => $boutId]) }}"
    data-url-complete="{{ route('mat.bout.complete', ['boutId' => $boutId]) }}"
    data-url-display-swap="{{ route('mat.bout.display-swap', ['boutId' => $boutId]) }}"
    data-initial-status="{{ $state->status }}"
    data-initial-display-swap="{{ $state->display_swap ? '1' : '0' }}"
    data-period-durations="{{ json_encode(array_merge(
        [ 1 => $periodDurations['1'] ?? 90, 2 => $periodDurations['2'] ?? 60, 3 => $periodDurations['3'] ?? 60 ],
        [ 4 => $periodDurations['OT1'] ?? 60, 5 => $periodDurations['OT2'] ?? 30, 6 => $periodDurations['OT3'] ?? 30 ]
    )) }}">
    {{-- Red panel (default: red) --}}
    <div class="col-red side-display-red" id="panel-red">
        <div class="wrestler-name">{{ $redWrestler->wr_first_name }} {{ $redWrestler->wr_last_name }} {{ $redWrestler->wr_club }}</div>
        <div class="score-header-row">
            <select id="stance-red" title="Stance"><option value="neutral">neutral</option><option value="top">top</option><option value="bottom">bottom</option></select>
            <select id="side-red" title="Side"><option value="red" selected>red</option><option value="green">green</option></select>
        </div>
        <div class="big-score" id="red-score">{{ $state->red_score }}</div>
        <div class="mat-score-grid">
            <button type="button" class="btn-num btn-score" data-side="red" data-event="caution" data-delta="-1">-1</button>
            <span class="score-label">Caution (Ca) <span class="pts">(0 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="caution" data-delta="1">+1</button>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="misconduct" data-delta="-2">-2</button>
            <span class="score-label">Misconduct (MC) <span class="pts">(0 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="misconduct" data-delta="2">+2</button>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="penalty1" data-delta="-3">-3</button>
            <span class="score-label">Penalty 1 (P1) <span class="pts">(1 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="penalty1" data-delta="3">+3</button>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="penalty2" data-delta="-4">-4</button>
            <span class="score-label">Penalty 2 (P2) <span class="pts">(2 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="penalty2" data-delta="4">+4</button>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="stalling" data-delta="-5">-5</button>
            <span><span class="score-label">Stalling (SW) <span class="pts">(0 pts)</span></span> <span class="score-label">Takedown 3 (T3) <span class="pts">(3 pts)</span></span></span>
            <button type="button" class="btn-num btn-score" data-side="red" data-event="takedown3" data-delta="5">+5</button>
        </div>
        <div class="timer-block" data-timer="blood_time_red">
            <div class="label">Blood Time:</div>
            <div class="display" id="blood-red">{{ $fmtTime($state->blood_time_red) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="blood_time_red">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="blood_time_red">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="blood_time_red">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="blood_time_red" data-default="300">Reset</button>
            </div>
        </div>
        <div class="timer-block" data-timer="injury_time_red">
            <div class="label">Injury Time:</div>
            <div class="display" id="injury-red">{{ $fmtTime($state->injury_time_red) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="injury_time_red">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="injury_time_red">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="injury_time_red">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="injury_time_red" data-default="90">Reset</button>
            </div>
        </div>
        @if($showHeadNeck ?? false)
        <div class="timer-block" data-timer="head_neck_time_red">
            <div class="label">Head/Neck:</div>
            <div class="display" id="head-neck-red">{{ $fmtTime($state->head_neck_time_red) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="head_neck_time_red">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="head_neck_time_red">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="head_neck_time_red">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="head_neck_time_red" data-default="300">Reset</button>
            </div>
        </div>
        @endif
        @if($showRecover ?? false)
        <div class="timer-block" data-timer="recovery_time_red">
            <div class="label">Recovery:</div>
            <div class="display" id="recovery-red">{{ $fmtTime($state->recovery_time_red) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="recovery_time_red">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="recovery_time_red">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="recovery_time_red">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="recovery_time_red" data-default="120">Reset</button>
            </div>
        </div>
        @endif
    </div>

    {{-- Center --}}
    <div class="col-center">
        <div id="completed-banner" style="display: none; margin: 0.5rem 0; padding: 0.5rem; background: #333; color: #9f9;">Bout completed</div>
        <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin: 0.5rem 0;">
            <button type="button" class="btn" id="period-prev">⇐ Prev</button>
            <select id="period-display">
                @php
                    $periodLabels = [ 1 => 'Period 1', 2 => 'Period 2', 3 => 'Period 3', 4 => 'OT1', 5 => 'OT2', 6 => 'OT3' ];
                @endphp
                @foreach($periodLabels as $p => $label)
                    <option value="{{ $p }}" {{ (int)$state->period === $p ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="button" class="btn" id="period-next">Next ⇒</button>
        </div>
        <div class="clock-display" id="clock-display">{{ $fmtTime($state->clock_seconds) }}</div>
        <div style="text-align: center; font-size: 0.9rem;">Clock</div>
        <div style="display: flex; justify-content: center; gap: 0.5rem; margin: 0.5rem 0; flex-wrap: wrap;">
            <button type="button" class="btn btn-success" id="clock-start">Start</button>
            <button type="button" class="btn btn-danger" id="clock-stop">Stop</button>
            <button type="button" class="btn" id="clock-set">Set</button>
            <button type="button" class="btn" id="clock-reset">Reset</button>
        </div>
        <div style="margin-top: 0.75rem;">
            <label for="comment-input">Comment</label>
            <input type="text" id="comment-input" placeholder="Add comment…" style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;">
            <button type="button" class="btn" id="comment-add" style="margin-top: 0.5rem;">Add Comment</button>
        </div>
        <div style="margin-top: 0.75rem;">
            <button type="button" class="btn" id="horn-btn">Sound Horn</button>
        </div>
        <div style="margin-top: 0.75rem;">
            <label>Complete bout</label>
            <select id="complete-winner" style="margin-top: 0.25rem; padding: 0.5rem;">
                <option value="">—</option>
                <option value="{{ $redWrestler->id }}">Red wins</option>
                <option value="{{ $greenWrestler->id }}">Green wins</option>
            </select>
            <input type="text" id="complete-result-type" placeholder="Result (e.g. Points, Fall)" style="margin-top: 0.25rem; padding: 0.5rem; width: 100%;">
            <button type="button" class="btn btn-success" id="complete-btn" style="margin-top: 0.5rem;">End bout</button>
        </div>
        <form method="post" action="{{ route('mat.bout.reset', ['boutId' => $boutId]) }}" style="margin-top: 0.75rem;" onsubmit="return confirm('Reset bout (scores, clock, period, timers)? Events will be kept.');">
            @csrf
            <button type="submit" class="btn btn-danger" id="reset-bout-btn">Reset Bout</button>
        </form>
    </div>

    {{-- Green panel (default: green) --}}
    <div class="col-green side-display-green" id="panel-green">
        <div class="wrestler-name">{{ $greenWrestler->wr_first_name }} {{ $greenWrestler->wr_last_name }} {{ $greenWrestler->wr_club }}</div>
        <div class="score-header-row">
            <select id="stance-green" title="Stance"><option value="neutral">neutral</option><option value="top">top</option><option value="bottom">bottom</option></select>
            <select id="side-green" title="Side"><option value="green" selected>green</option><option value="red">red</option></select>
        </div>
        <div class="big-score" id="green-score">{{ $state->green_score }}</div>
        <div class="mat-score-grid">
            <button type="button" class="btn-num btn-score" data-side="green" data-event="caution" data-delta="-1">-1</button>
            <span class="score-label">Caution (Ca) <span class="pts">(0 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="caution" data-delta="1">+1</button>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="misconduct" data-delta="-2">-2</button>
            <span class="score-label">Misconduct (MC) <span class="pts">(0 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="misconduct" data-delta="2">+2</button>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="penalty1" data-delta="-3">-3</button>
            <span class="score-label">Penalty 1 (P1) <span class="pts">(1 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="penalty1" data-delta="3">+3</button>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="penalty2" data-delta="-4">-4</button>
            <span class="score-label">Penalty 2 (P2) <span class="pts">(2 pts)</span></span>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="penalty2" data-delta="4">+4</button>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="stalling" data-delta="-5">-5</button>
            <span><span class="score-label">Stalling (SW) <span class="pts">(0 pts)</span></span> <span class="score-label">Takedown 3 (T3) <span class="pts">(3 pts)</span></span></span>
            <button type="button" class="btn-num btn-score" data-side="green" data-event="takedown3" data-delta="5">+5</button>
        </div>
        <div class="timer-block" data-timer="blood_time_green">
            <div class="label">Blood Time:</div>
            <div class="display" id="blood-green">{{ $fmtTime($state->blood_time_green) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="blood_time_green">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="blood_time_green">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="blood_time_green">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="blood_time_green" data-default="300">Reset</button>
            </div>
        </div>
        <div class="timer-block" data-timer="injury_time_green">
            <div class="label">Injury Time:</div>
            <div class="display" id="injury-green">{{ $fmtTime($state->injury_time_green) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="injury_time_green">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="injury_time_green">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="injury_time_green">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="injury_time_green" data-default="90">Reset</button>
            </div>
        </div>
        @if($showHeadNeck ?? false)
        <div class="timer-block" data-timer="head_neck_time_green">
            <div class="label">Head/Neck:</div>
            <div class="display" id="head-neck-green">{{ $fmtTime($state->head_neck_time_green) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="head_neck_time_green">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="head_neck_time_green">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="head_neck_time_green">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="head_neck_time_green" data-default="300">Reset</button>
            </div>
        </div>
        @endif
        @if($showRecover ?? false)
        <div class="timer-block" data-timer="recovery_time_green">
            <div class="label">Recovery:</div>
            <div class="display" id="recovery-green">{{ $fmtTime($state->recovery_time_green) }}</div>
            <div class="timer-btns">
                <button type="button" class="btn btn-success btn-timer-start" data-timer="recovery_time_green">Start</button>
                <button type="button" class="btn btn-danger btn-timer-stop" data-timer="recovery_time_green">Stop</button>
                <button type="button" class="btn btn-timer-set" data-timer="recovery_time_green">Set</button>
                <button type="button" class="btn btn-timer-reset" data-timer="recovery_time_green" data-default="120">Reset</button>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="mat-events">
    <h3>Event log</h3>
    <ul id="events-list">
        @foreach($events as $e)
            <li><span class="side-{{ $e->side }}">{{ $e->side }}</span> {{ $e->event_type }}@if($e->points) ({{ $e->points }})@endif @if($e->note) — {{ $e->note }}@endif</li>
        @endforeach
    </ul>
</div>

    </div>
</div>

<script>
(function() {
    var el = document.getElementById('mat-scoring');
    if (!el) return;
    var csrf = el.getAttribute('data-csrf');
    var urls = {
        state: el.getAttribute('data-url-state'),
        clock: el.getAttribute('data-url-clock'),
        period: el.getAttribute('data-url-period'),
        event: el.getAttribute('data-url-event'),
        comment: el.getAttribute('data-url-comment'),
        timer: el.getAttribute('data-url-timer'),
        complete: el.getAttribute('data-url-complete'),
        displaySwap: el.getAttribute('data-url-display-swap')
    };
    var headers = { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' };

    function fmt(sec) {
        sec = Math.max(0, Math.floor(sec));
        var m = Math.floor(sec / 60), s = sec % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    var clockInterval = null;
    var timerIntervals = {};

    function applyState(d) {
        document.getElementById('red-score').textContent = d.red_score;
        document.getElementById('green-score').textContent = d.green_score;
        document.getElementById('clock-display').textContent = fmt(d.clock_seconds);
        document.getElementById('period-display').value = d.period;
        clockSec = d.clock_seconds;
        document.getElementById('blood-red').textContent = fmt(d.blood_time_red);
        document.getElementById('blood-green').textContent = fmt(d.blood_time_green);
        document.getElementById('injury-red').textContent = fmt(d.injury_time_red);
        document.getElementById('injury-green').textContent = fmt(d.injury_time_green);
        var hnRed = document.getElementById('head-neck-red'); if (hnRed) hnRed.textContent = fmt(d.head_neck_time_red);
        var hnGreen = document.getElementById('head-neck-green'); if (hnGreen) hnGreen.textContent = fmt(d.head_neck_time_green);
        var recRed = document.getElementById('recovery-red'); if (recRed) recRed.textContent = fmt(d.recovery_time_red);
        var recGreen = document.getElementById('recovery-green'); if (recGreen) recGreen.textContent = fmt(d.recovery_time_green);
        var list = document.getElementById('events-list');
        list.innerHTML = d.events.map(function(e) {
            var pt = e.points ? ' (' + e.points + ')' : '';
            var note = e.note ? ' — ' + e.note : '';
            return '<li><span class="side-' + e.side + '">' + e.side + '</span> ' + e.event_type + pt + note + '</li>';
        }).join('');
        if (d.status === 'completed') {
            document.getElementById('completed-banner').style.display = 'block';
            el.querySelectorAll('button, select, input').forEach(function(b) {
                b.disabled = true;
            });
            if (clockInterval) clearInterval(clockInterval);
            clockInterval = null;
            Object.keys(timerIntervals).forEach(function(k) { clearInterval(timerIntervals[k]); });
            timerIntervals = {};
        }
    }

    function fetchState(cb) {
        fetch(urls.state, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(d) { applyState(d); if (cb) cb(d); })
            .catch(function() { if (cb) cb(null); });
    }

    function post(url, body, cb) {
        fetch(url, { method: 'POST', headers: headers, body: JSON.stringify(body), credentials: 'same-origin' })
            .then(function(r) { return r.json().catch(function() { return null; }); })
            .then(function(d) { if (d && !d.error) applyState(d); if (cb) cb(d); });
    }

    // Clock
    var clockSec = {{ (int) $state->clock_seconds }};
    document.getElementById('clock-start').onclick = function() {
        post(urls.clock, { action: 'start' }, function() {
            if (clockInterval) clearInterval(clockInterval);
            clockInterval = setInterval(function() {
                clockSec = Math.max(0, clockSec - 1);
                document.getElementById('clock-display').textContent = fmt(clockSec);
                if (clockSec > 0) {
                    post(urls.clock, { action: 'set', clock_seconds: clockSec });
                }
            }, 1000);
        });
    };
    document.getElementById('clock-stop').onclick = function() {
        if (clockInterval) { clearInterval(clockInterval); clockInterval = null; }
        post(urls.clock, { action: 'stop' }, function(d) { if (d && d.clock_seconds !== undefined) clockSec = d.clock_seconds; });
    };
    document.getElementById('clock-set').onclick = function() {
        var v = prompt('Clock (M:SS or seconds)', fmt(clockSec));
        if (v === null) return;
        var parts = (v + '').split(':');
        var sec = parts.length === 2 ? parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10) : parseInt(v, 10);
        if (!isNaN(sec) && sec >= 0) { clockSec = sec; post(urls.clock, { action: 'set', clock_seconds: sec }, function(d) { if (d) clockSec = d.clock_seconds; }); }
    };
    document.getElementById('clock-reset').onclick = function() {
        var p = parseInt(document.getElementById('period-display').value, 10);
        clockSec = periodDurations[p] !== undefined ? periodDurations[p] : 0;
        post(urls.clock, { action: 'set', clock_seconds: clockSec });
    };

    // Period
    var periodDurations = {};
    try {
        var pd = el.getAttribute('data-period-durations');
        if (pd) periodDurations = JSON.parse(pd);
    } catch (e) {}

    document.getElementById('period-prev').onclick = function() {
        var p = Math.max(1, parseInt(document.getElementById('period-display').value, 10) - 1);
        post(urls.period, { period: p }, function(d) { if (d) { document.getElementById('period-display').value = d.period; clockSec = d.clock_seconds; } });
    };
    document.getElementById('period-next').onclick = function() {
        post(urls.period, { action: 'next' }, function(d) {
            if (d && d.error) return;
            if (d) {
                document.getElementById('period-display').value = d.period;
                clockSec = d.clock_seconds;
            }
        });
    };
    document.getElementById('period-display').onchange = function() {
        var p = parseInt(this.value, 10);
        post(urls.period, { period: p }, function(d) { if (d) clockSec = d.clock_seconds; });
    };

    // Side assignment: one choice sets both wrestlers. Wrestler A (left) = selected side, Wrestler B (right) = the other.
    var leftPanelSide = 'red'; // wrestler A is red by default; wrestler B is green
    function applySideAssignment() {
        var rightPanelSide = leftPanelSide === 'red' ? 'green' : 'red';
        // Left panel (wrestler A)
        var panelRed = document.getElementById('panel-red');
        var sideRed = document.getElementById('side-red');
        if (panelRed && sideRed) {
            panelRed.classList.remove('side-display-red', 'side-display-green');
            panelRed.classList.add('side-display-' + leftPanelSide);
            sideRed.value = leftPanelSide;
            panelRed.querySelectorAll('.btn-score').forEach(function(btn) { btn.setAttribute('data-side', leftPanelSide); });
        }
        // Right panel (wrestler B)
        var panelGreen = document.getElementById('panel-green');
        var sideGreen = document.getElementById('side-green');
        if (panelGreen && sideGreen) {
            panelGreen.classList.remove('side-display-red', 'side-display-green');
            panelGreen.classList.add('side-display-' + rightPanelSide);
            sideGreen.value = rightPanelSide;
            panelGreen.querySelectorAll('.btn-score').forEach(function(btn) { btn.setAttribute('data-side', rightPanelSide); });
        }
    }
    document.getElementById('side-red').onchange = function() {
        leftPanelSide = this.value;
        applySideAssignment();
        if (urls.displaySwap) post(urls.displaySwap, { display_swap: leftPanelSide === 'green' });
    };
    document.getElementById('side-green').onchange = function() {
        leftPanelSide = this.value === 'red' ? 'green' : 'red';
        applySideAssignment();
        if (urls.displaySwap) post(urls.displaySwap, { display_swap: leftPanelSide === 'green' });
    };
    applySideAssignment();
    var initialSwap = el.getAttribute('data-initial-display-swap');
    if (initialSwap === '1') { leftPanelSide = 'green'; applySideAssignment(); }

    // Score buttons: data-delta is the exact points to add (+) or subtract (-)
    el.querySelectorAll('.btn-score').forEach(function(btn) {
        btn.onclick = function() {
            var side = btn.getAttribute('data-side');
            var event = btn.getAttribute('data-event');
            var delta = parseInt(btn.getAttribute('data-delta'), 10);
            if (isNaN(delta)) return;
            post(urls.event, { side: side, event_type: event, points: delta });
        };
    });

    // Comment
    document.getElementById('comment-add').onclick = function() {
        var note = document.getElementById('comment-input').value.trim();
        if (!note) return;
        post(urls.comment, { note: note }, function() { document.getElementById('comment-input').value = ''; });
    };

    // Side timers
    function runSideTimer(timerKey, displayId) {
        var disp = document.getElementById(displayId);
        if (!disp) return;
        var t = disp.textContent.trim().split(':');
        var m = parseInt(t[0], 10) || 0;
        var s = parseInt(t[1], 10) || 0;
        var sec = m * 60 + s;
        if (timerIntervals[timerKey]) clearInterval(timerIntervals[timerKey]);
        timerIntervals[timerKey] = setInterval(function() {
            sec = Math.max(0, sec - 1);
            disp.textContent = fmt(sec);
            if (sec % 10 === 0 && sec > 0) post(urls.timer, { timer: timerKey, seconds: sec });
        }, 1000);
    }
    function stopSideTimer(timerKey, displayId) {
        if (timerIntervals[timerKey]) { clearInterval(timerIntervals[timerKey]); timerIntervals[timerKey] = null; }
        var disp = document.getElementById(displayId);
        if (disp) {
            var t = disp.textContent.split(':');
            var sec = (parseInt(t[0], 10) || 0) * 60 + (parseInt(t[1], 10) || 0);
            post(urls.timer, { timer: timerKey, seconds: sec });
        }
    }
    var timerDisplayIds = { blood_time_red: 'blood-red', blood_time_green: 'blood-green', injury_time_red: 'injury-red', injury_time_green: 'injury-green', head_neck_time_red: 'head-neck-red', head_neck_time_green: 'head-neck-green', recovery_time_red: 'recovery-red', recovery_time_green: 'recovery-green' };
    el.querySelectorAll('.btn-timer-start').forEach(function(btn) {
        btn.onclick = function() { runSideTimer(btn.getAttribute('data-timer'), timerDisplayIds[btn.getAttribute('data-timer')]); };
    });
    el.querySelectorAll('.btn-timer-stop').forEach(function(btn) {
        btn.onclick = function() { stopSideTimer(btn.getAttribute('data-timer'), timerDisplayIds[btn.getAttribute('data-timer')]); };
    });
    el.querySelectorAll('.btn-timer-set').forEach(function(btn) {
        btn.onclick = function() {
            var key = btn.getAttribute('data-timer');
            var disp = document.getElementById(timerDisplayIds[key]);
            var v = prompt('Seconds remaining', disp ? disp.textContent : '5:00');
            if (v === null) return;
            var parts = (v + '').split(':');
            var sec = parts.length === 2 ? parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10) : parseInt(v, 10);
            if (!isNaN(sec) && sec >= 0) post(urls.timer, { timer: key, seconds: sec });
        };
    });
    el.querySelectorAll('.btn-timer-reset').forEach(function(btn) {
        btn.onclick = function() {
            var key = btn.getAttribute('data-timer');
            var def = parseInt(btn.getAttribute('data-default'), 10) || 0;
            post(urls.timer, { timer: key, seconds: def });
        };
    });

    document.getElementById('horn-btn').onclick = function() { /* placeholder */ };

    document.getElementById('complete-btn').onclick = function() {
        var winnerId = document.getElementById('complete-winner').value;
        var resultType = document.getElementById('complete-result-type').value.trim() || null;
        post(urls.complete, { winner_id: winnerId || null, result_type: resultType });
    };

    if (el.getAttribute('data-initial-status') === 'completed') {
        fetchState();
    }
})();
</script>
@endsection
