<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Print Scan Sheet – {{ $tournament->TournamentName }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; margin: 1rem; }
        .no-print { margin-bottom: 1rem; }
        h1 { font-size: 1.25rem; margin: 0 0 0.25rem; text-align: center; }
        .subtitle { text-align: center; color: #555; margin-bottom: 1.5rem; font-size: 0.95rem; }
        .row { display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: flex-start; }
        .col { flex: 1; min-width: 200px; }
        .step-title { font-size: 1.1rem; font-weight: bold; margin: 0 0 0.5rem; }
        .step-body { margin: 0; font-size: 0.95rem; line-height: 1.4; }
        .qr-wrap { text-align: center; margin-bottom: 0.75rem; }
        .qr-wrap img { display: block; margin: 0 auto 0.5rem; width: 160px; height: 160px; }
        .qr-wrap .brand { font-weight: bold; font-size: 1rem; }
        .search-box { background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; padding: 1rem; margin: 0.5rem 0; }
        .search-box .info { font-size: 0.9rem; margin-bottom: 0.5rem; }
        .search-box label { display: block; font-size: 0.9rem; margin-bottom: 0.25rem; }
        .search-box input[type="text"] { width: 100%; padding: 0.4rem; border: 1px solid #999; border-radius: 3px; margin-bottom: 0.5rem; }
        .search-box .btn { padding: 0.4rem 1rem; background: #3498db; color: #fff; border: 0; border-radius: 3px; cursor: pointer; font-size: 0.9rem; }
        .bracket-header { font-size: 1rem; font-weight: bold; margin: 0 0 0.5rem; }
        .bout-card { margin-bottom: 0.5rem; }
        .bout-row { display: flex; gap: 2px; margin-bottom: 2px; }
        .bout-cell { border: 1px solid #333; padding: 0.35rem 0.5rem; font-size: 0.85rem; }
        .bout-cell.name { flex: 1; }
        .bout-cell.score { width: 2.5rem; text-align: center; }
        .bout-cell.club { flex: 1; }
        .bout-cell.bout-num { min-width: 3.5rem; text-align: center; font-weight: bold; }
        @media print { body { margin: 0.5in; } .no-print { display: none !important; } .row { break-inside: avoid; } }
    </style>
</head>
<body>
    <p class="no-print"><a href="{{ route('manage.view.summary', $tournament->id) }}">← Back</a></p>

    <h1>{{ $tournament->TournamentName }}</h1>
    <p class="subtitle">{{ $tournament->TournamentDate->format('l, F j, Y') }}</p>

    <div class="row">
        <div class="col">
            <div class="qr-wrap">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&amp;data={{ urlencode($searchUrl) }}" alt="QR Code" width="160" height="160">
                <span class="brand">AutoWrestle</span>
            </div>
            <p class="step-title">Step One</p>
            <p class="step-body">Scan the barcode to locate the tournament on your phone.</p>
        </div>

        <div class="col">
            <p class="step-title">Search Wrestlers</p>
            <div class="search-box">
                <p class="info">Information</p>
                <p class="step-body">Type the last name.</p>
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" placeholder="" disabled>
                <button type="button" class="btn" disabled>Search</button>
            </div>
            <p class="step-title">Step Two</p>
            <p class="step-body">Search for your wrestler by last name. Remember, less is best when searching. We suggest using only the first couple of letters.</p>
        </div>

        <div class="col">
            <p class="bracket-header">Bracket: 34</p>
            <p class="step-body" style="margin-bottom: 0.5rem;">Name &nbsp; Score &nbsp; Bout</p>
            <div class="bout-card">
                <div class="bout-row">
                    <span class="bout-cell name">Jonathan Pillai</span>
                    <span class="bout-cell score">0</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell club">LCA</span>
                    <span class="bout-cell bout-num">1001</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell name">Tyler White</span>
                    <span class="bout-cell score">0</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell club">Celtic Wrestling Club</span>
                </div>
            </div>
            <div class="bout-card">
                <div class="bout-row">
                    <span class="bout-cell name">Jonathan Pillai</span>
                    <span class="bout-cell score">0</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell club">LCA</span>
                    <span class="bout-cell bout-num">1016</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell name">Tye Henderson</span>
                    <span class="bout-cell score">0</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell club">HCYWC</span>
                </div>
            </div>
            <div class="bout-card">
                <div class="bout-row">
                    <span class="bout-cell name">Jonathan Pillai</span>
                    <span class="bout-cell score">0</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell club">LCA</span>
                    <span class="bout-cell bout-num">1031</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell name">Tyler Cobb</span>
                    <span class="bout-cell score">0</span>
                </div>
                <div class="bout-row">
                    <span class="bout-cell club">Celtic Wrestling Club</span>
                </div>
            </div>
            <p class="step-title">Step Three</p>
            <p class="step-body">Click on your wrestler to view the bout numbers.</p>
        </div>
    </div>
</body>
</html>
