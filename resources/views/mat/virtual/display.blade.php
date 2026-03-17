<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Score display – Audience</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Lato', Arial, sans-serif; background: #000; color: #fff; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 20px; }
        #virtual-root { text-align: center; width: 100%; max-width: 1200px; }
        .virtual-top { font-size: 0.6em; color: #fff; margin-bottom: 0.2em; }
        .virtual-row-three { display: flex; align-items: stretch; justify-content: center; gap: 0.4em; margin-top: -0.15em; }
        .virtual-col { flex: 1; max-width: 320px; display: flex; flex-direction: column; align-items: center; gap: 0.05em; }
        .virtual-col .virtual-name-wrap { min-height: 2.2em; display: flex; align-items: flex-end; justify-content: center; text-align: center; padding-bottom: 0.1em; }
        .virtual-col .virtual-score-box { width: 100%; min-width: 4.5em; min-height: 2em; display: flex; align-items: center; justify-content: center; padding: 0.35em 0.75em; border-radius: 8px; font-weight: 700; flex-shrink: 0; overflow: visible; }
        .virtual-score-box.red { background: #c00; color: #fff; }
        .virtual-score-box.green { background: #080; color: #fff; }
        .virtual-score-num { font-size: 3.5em; line-height: 1.1; font-weight: 800; white-space: nowrap; }
        .virtual-center { display: flex; flex-direction: column; align-items: center; min-width: 2.5em; }
        .virtual-timer { font-size: 1.4em; font-weight: 800; color: #fff; }
        .virtual-period { font-size: 0.4em; color: #ff0; margin-top: 0.25em; }
        .virtual-name { font-size: 0.5em; text-align: center; font-weight: 600; }
        .virtual-name.red { color: #f66; }
        .virtual-name.green { color: #6f6; }
        .virtual-extra-timers { display: flex; justify-content: center; gap: 2em; margin-top: 0.4em; font-size: 0.35em; color: #ccc; }
        .virtual-extra-timers .virtual-timer-group { display: flex; flex-direction: column; align-items: center; gap: 0.2em; }
        .virtual-extra-timers .virtual-timer-label { font-weight: 600; }
        .virtual-extra-timers .virtual-timer-values { display: flex; gap: 1em; }
        .virtual-extra-timers .virtual-timer-red { color: #f99; }
        .virtual-extra-timers .virtual-timer-green { color: #9f9; }
    </style>
</head>
<body>
    @php
        $showHeadNeck = $showHeadNeck ?? false;
        $showRecover = $showRecover ?? false;
        $fmtTime = function ($sec) { $sec = max(0, (int)$sec); return sprintf('%d:%02d', (int)($sec / 60), $sec % 60); };
        $swap = $initial['display_swap'] ?? false;
        // Red always left, green always right. When swap: wrestler 2 (green) + his score go to left (red side), wrestler 1 (red) + his score go to right (green side).
        $leftName = $swap ? ($initial['green_name'] ?? 'Unknown') : ($initial['red_name'] ?? 'Unknown');
        $rightName = $swap ? ($initial['red_name'] ?? 'Unknown') : ($initial['green_name'] ?? 'Unknown');
        $leftScore = $swap ? ($initial['green_score'] ?? 0) : ($initial['red_score'] ?? 0);
        $rightScore = $swap ? ($initial['red_score'] ?? 0) : ($initial['green_score'] ?? 0);
    @endphp
    <div id="virtual-root" style="font-size: {{ $fontPx }}px;">
        <div class="virtual-top" id="virtual-top">{{ $boutId ? 'Bout ' . $boutId : 'n/a - 0' }}</div>
        <div class="virtual-row-three">
            <div class="virtual-col" id="virtual-col-left">
                <div class="virtual-name-wrap"><div class="virtual-name red" id="virtual-left-name">{{ $leftName }}</div></div>
                <div class="virtual-score-box red" id="virtual-left-score-box"><span class="virtual-score-num" id="virtual-left-score">{{ $leftScore }}</span></div>
            </div>
            <div class="virtual-center">
                <div class="virtual-timer" id="virtual-timer">{{ sprintf('%d:%02d', (int)(($initial['clock_seconds'] ?? 0) / 60), ($initial['clock_seconds'] ?? 0) % 60) }}</div>
                <div class="virtual-period" id="virtual-period">{{ $periodLabel }}</div>
                @if($showHeadNeck)
                <div class="virtual-extra-timers" id="virtual-head-neck-row">
                    <div class="virtual-timer-group">
                        <span class="virtual-timer-label">Head/Neck</span>
                        <div class="virtual-timer-values">
                            <span class="virtual-timer-red" id="virtual-head-neck-red">{{ $fmtTime($initial['head_neck_time_red'] ?? 0) }}</span>
                            <span class="virtual-timer-green" id="virtual-head-neck-green">{{ $fmtTime($initial['head_neck_time_green'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                @endif
                @if($showRecover)
                <div class="virtual-extra-timers" id="virtual-recover-row">
                    <div class="virtual-timer-group">
                        <span class="virtual-timer-label">Recovery</span>
                        <div class="virtual-timer-values">
                            <span class="virtual-timer-red" id="virtual-recover-red">{{ $fmtTime($initial['recovery_time_red'] ?? 0) }}</span>
                            <span class="virtual-timer-green" id="virtual-recover-green">{{ $fmtTime($initial['recovery_time_green'] ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="virtual-col" id="virtual-col-right">
                <div class="virtual-name-wrap"><div class="virtual-name green" id="virtual-right-name">{{ $rightName }}</div></div>
                <div class="virtual-score-box green" id="virtual-right-score-box"><span class="virtual-score-num" id="virtual-right-score">{{ $rightScore }}</span></div>
            </div>
        </div>
    </div>

    @if($stateUrl)
    <script>
    (function() {
        var periodLabels = { 1: 'Period 1', 2: 'Period 2', 3: 'Period 3', 4: 'OT1', 5: 'OT2', 6: 'OT3' };
        function fmt(sec) {
            sec = Math.max(0, Math.floor(sec));
            var m = Math.floor(sec / 60), s = sec % 60;
            return m + ':' + (s < 10 ? '0' : '') + s;
        }
        function poll() {
            var url = '{{ $stateUrl }}?t=' + Date.now();
            fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d && !d.error) {
                        var swap = !!(d.display_swap === true || d.display_swap === 1);
                        // Red always left, green always right. When swap: WR2 + his score on left, WR1 + his score on right.
                        var leftScore = swap ? d.green_score : d.red_score;
                        var rightScore = swap ? d.red_score : d.green_score;
                        var leftName = swap ? (d.green_name || 'Unknown') : (d.red_name || 'Unknown');
                        var rightName = swap ? (d.red_name || 'Unknown') : (d.green_name || 'Unknown');
                        document.getElementById('virtual-left-score').textContent = leftScore;
                        document.getElementById('virtual-right-score').textContent = rightScore;
                        document.getElementById('virtual-left-name').textContent = leftName;
                        document.getElementById('virtual-right-name').textContent = rightName;
                        document.getElementById('virtual-timer').textContent = fmt(d.clock_seconds);
                        document.getElementById('virtual-period').textContent = periodLabels[d.period] || ('Period ' + d.period);
                        var hnRed = document.getElementById('virtual-head-neck-red');
                        if (hnRed) { hnRed.textContent = fmt(d.head_neck_time_red != null ? d.head_neck_time_red : 0); }
                        var hnGreen = document.getElementById('virtual-head-neck-green');
                        if (hnGreen) { hnGreen.textContent = fmt(d.head_neck_time_green != null ? d.head_neck_time_green : 0); }
                        var recRed = document.getElementById('virtual-recover-red');
                        if (recRed) { recRed.textContent = fmt(d.recovery_time_red != null ? d.recovery_time_red : 0); }
                        var recGreen = document.getElementById('virtual-recover-green');
                        if (recGreen) { recGreen.textContent = fmt(d.recovery_time_green != null ? d.recovery_time_green : 0); }
                        if (d.bout_id != null) {
                            document.getElementById('virtual-top').textContent = 'Bout ' + d.bout_id;
                        } else {
                            document.getElementById('virtual-top').textContent = 'n/a - 0';
                        }
                    }
                })
                .catch(function() {});
        }
        setInterval(poll, 500);
        poll();
    })();
    </script>
    @endif
</body>
</html>
