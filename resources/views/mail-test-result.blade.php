<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mail test</title>
    <style>
        body { font-family: sans-serif; max-width: 640px; margin: 2rem auto; padding: 0 1rem; }
        .ok { color: #0a0; }
        .err { color: #c00; }
        pre { background: #f5f5f5; padding: 1rem; overflow-x: auto; font-size: 0.85rem; }
    </style>
</head>
<body>
    <h1>Mail test</h1>
    @if($success)
        <p class="ok">{{ $message }}</p>
    @else
        <p class="err">{{ $message }}</p>
        @if(!empty($trace))
            <h2>Stack trace</h2>
            <pre>{{ $trace }}</pre>
        @endif
    @endif
    <p><a href="{{ url('/') }}">← Home</a></p>
</body>
</html>
