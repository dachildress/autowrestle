{{--
  Mat-side layout: scoring, match list, virtual settings, etc.
  DO NOT change this layout or the mat-side scoring interface.
  Isolated so the main app redesign does not affect mat pages.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <title>@yield('title', 'AutoWrestle') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Lato', sans-serif; margin: 0; background: #ecf0f1; color: #2c3e50; line-height: 1.5; }
        .container { max-width: 1170px; margin: 0 auto; padding: 0 15px; }
        .main { padding: 0 15px 2rem; }
        .panel { margin-bottom: 21px; background: #fff; border: 1px solid #ecf0f1; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
        .panel-heading { padding: 10px 15px; background: #ecf0f1; border-bottom: 1px solid #ecf0f1; border-radius: 3px 3px 0 0; font-size: 17px; color: #2c3e50; }
        .panel-body { padding: 15px; }
        h1, .panel-title { margin: 0 0 1rem; font-size: 1.75rem; color: #2c3e50; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ecf0f1; padding: 0.5rem 0.75rem; text-align: left; }
        th { background: #ecf0f1; font-weight: 600; color: #2c3e50; }
        .table-dark thead th { background: #2c3e50; color: #fff; border-color: #2c3e50; }
        tbody tr:nth-child(odd) { background: #ccc; }
        tbody tr:nth-child(even) { background: #fff; }
        a { color: #2c3e50; text-decoration: none; }
        a:hover { color: #18bc9c; text-decoration: underline; }
        .error { color: #e74c3c; }
        .success { color: #18bc9c; }
        .error-list { color: #e74c3c; margin: 0 0 1rem; padding-left: 1.25rem; }
        .btn { display: inline-block; font-weight: bold; text-decoration: none; background: #5879ad; color: #fff; padding: 6px 12px; border: 1px solid #333; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #4a6a9a; color: #fff; }
        .btn-primary { background: #2c3e50; border-color: #2c3e50; }
        .btn-success { background: #18bc9c; border-color: #18bc9c; color: #fff; }
        .btn-danger { background: #e74c3c; border-color: #e74c3c; color: #fff; }
        .btn-block { display: block; text-align: center; }
        .form-horizontal .form-group { margin-bottom: 1rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
        .form-horizontal .form-group label { width: 140px; flex-shrink: 0; }
        .form-horizontal .form-group input, .form-horizontal .form-group select { flex: 1; min-width: 180px; padding: 0.5rem; border: 1px solid #ecf0f1; border-radius: 4px; }
        .form-horizontal .form-actions { margin-top: 1rem; padding-left: 140px; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .row .col { padding: 0 15px; flex: 1; min-width: 0; }
        @media (max-width: 768px) { .row .col { flex: 0 0 100%; max-width: 100%; } }
        @stack('styles')
    </style>
</head>
<body class="mat-page">
    <div class="main" style="max-width: none; padding: 0;">
        @yield('content')
    </div>
    <script>
    (function() {
        function initVirtualPopup() {
            document.querySelectorAll('a[data-virtual-url]').forEach(function(a) {
                if (a._virtualPopup) return;
                a._virtualPopup = true;
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    var url = a.getAttribute('data-virtual-url');
                    window.open(url, 'virtual', 'width=640,height=480,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes');
                });
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initVirtualPopup);
        } else {
            initVirtualPopup();
        }
    })();
    </script>
</body>
</html>
