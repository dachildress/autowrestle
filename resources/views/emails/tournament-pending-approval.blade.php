<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tournament pending approval</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #334155; max-width: 600px; margin: 0 auto; padding: 1rem;">
    <h1 style="font-size: 1.25rem; color: #0f172a;">Tournament pending approval</h1>
    <p>A new tournament has been created and is waiting for administrator approval.</p>
    <p><strong>{{ $tournament->TournamentName }}</strong><br>
        Date: {{ $tournament->TournamentDate ? $tournament->TournamentDate->format('F j, Y') : '—' }}<br>
        @if($tournament->city || $tournament->state)
            {{ trim(implode(', ', array_filter([$tournament->city, $tournament->state]))) }}
        @endif
    </p>
    <p>
        <a href="{{ url('/tournaments/manage') }}" style="display: inline-block; padding: 0.5rem 1rem; background: #0f172a; color: white; text-decoration: none; border-radius: 0.375rem;">Go to Manage Tournaments</a>
    </p>
    <p style="font-size: 0.875rem; color: #64748b;">Log in and approve the tournament from the Manage Tournaments page so it can appear on the public site.</p>
</body>
</html>
