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
    </style>
</head>
<body>
    @php
        $swap = $initial['display_swap'] ?? false;
        // Red always left, green always right. Scores stay in place; only names swap when scorer switches colors.
        $leftName = $swap ? trim(($initial['green_name'] ?? '') . ' ' . ($initial['green_team'] ?? '')) : trim(($initial['red_name'] ?? '') . ' ' . ($initial['red_team'] ?? ''));
        $rightName = $swap ? trim(($initial['red_name'] ?? '') . ' ' . ($initial['red_team'] ?? '')) : trim(($initial['green_name'] ?? '') . ' ' . ($initial['green_team'] ?? ''));
    @endphp
    <div id="virtual-root" style="font-size: {{ $fontPx }}px;">
        <div class="virtual-top" id="virtual-top">{{ $boutId ? 'Bout ' . $boutId : 'n/a - 0' }}</div>
        <div class="virtual-row-three">
            <div class="virtual-col" id="virtual-col-left">
                <div class="virtual-name-wrap"><div class="virtual-name red" id="virtual-left-name">{{ $leftName ?: 'Unknown' }}</div></div>
                <div class="virtual-score-box red" id="virtual-left-score-box"><span class="virtual-score-num" id="virtual-left-score">{{ $initial['red_score'] ?? 0 }}</span></div>
            </div>
            <div class="virtual-center">
                <div class="virtual-timer" id="virtual-timer">{{ sprintf('%d:%02d', (int)(($initial['clock_seconds'] ?? 0) / 60), ($initial['clock_seconds'] ?? 0) % 60) }}</div>
                <div class="virtual-period" id="virtual-period">{{ $periodLabel }}</div>
            </div>
            <div class="virtual-col" id="virtual-col-right">
                <div class="virtual-name-wrap"><div class="virtual-name green" id="virtual-right-name">{{ $rightName ?: 'Unknown' }}</div></div>
                <div class="virtual-score-box green" id="virtual-right-score-box"><span class="virtual-score-num" id="virtual-right-score">{{ $initial['green_score'] ?? 0 }}</span></div>
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
                        // Red always left, green always right. Scores stay in place; only names move when scorer switches colors.
                        document.getElementById('virtual-left-score').textContent = d.red_score;
                        document.getElementById('virtual-right-score').textContent = d.green_score;
                        var leftName = swap ? (d.green_name + ' ' + (d.green_team || '')).trim() : (d.red_name + ' ' + (d.red_team || '')).trim();
                        var rightName = swap ? (d.red_name + ' ' + (d.red_team || '')).trim() : (d.green_name + ' ' + (d.green_team || '')).trim();
                        document.getElementById('virtual-left-name').textContent = leftName || 'Unknown';
                        document.getElementById('virtual-right-name').textContent = rightName || 'Unknown';
                        document.getElementById('virtual-timer').textContent = fmt(d.clock_seconds);
                        document.getElementById('virtual-period').textContent = periodLabels[d.period] || ('Period ' + d.period);
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
