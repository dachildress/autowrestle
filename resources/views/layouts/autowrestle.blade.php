<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AutoWrestle') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Lato', sans-serif; margin: 0; background: #ecf0f1; color: #2c3e50; line-height: 1.5; }
        .container { max-width: 1170px; margin: 0 auto; padding: 0 15px; }
        .nav-bar { background: #2c3e50; min-height: 60px; margin-bottom: 21px; border: 0; }
        .nav-bar .wrap { max-width: 1170px; margin: 0 auto; padding: 0 15px; display: flex; align-items: center; min-height: 60px; gap: 1rem; flex-wrap: wrap; }
        .nav-bar a { color: #fff; text-decoration: none; padding: 19px 10px; display: inline-block; }
        .nav-bar a:hover { color: #18bc9c; background: transparent; }
        .nav-bar .brand { font-weight: 700; font-size: 19px; padding: 11px 15px 11px 0; }
        .nav-bar .spacer { flex: 1; }
        .nav-bar .user { color: #fff; font-size: 1rem; padding: 19px 10px 19px 0; }
        .nav-bar .user a { color: #fff; padding: 0; }
        .nav-bar form button { color: #fff; background: 0; border: 0; cursor: pointer; font: inherit; padding: 19px 10px; }
        .nav-bar form button:hover { color: #18bc9c; }
        .main { padding: 0 15px 2rem; }
        .panel { margin-bottom: 21px; background: #fff; border: 1px solid #ecf0f1; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
        .panel-heading { padding: 10px 15px; background: #ecf0f1; border-bottom: 1px solid #ecf0f1; border-radius: 3px 3px 0 0; font-size: 17px; color: #2c3e50; }
        .panel-body { padding: 15px; }
        h1, .panel-title { margin: 0 0 1rem; font-size: 1.75rem; color: #2c3e50; }
        .panel-heading + .panel-body h1 { margin-top: 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ecf0f1; padding: 0.5rem 0.75rem; text-align: left; }
        th { background: #ecf0f1; font-weight: 600; color: #2c3e50; }
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
        .btn-primary:hover { background: #1a242f; color: #fff; }
        .btn-block { display: block; text-align: center; }
        .btn-success { background: #18bc9c; border-color: #18bc9c; color: #fff; }
        .btn-success:hover { background: #15a589; color: #fff; }
        .btn-danger { background: #e74c3c; border-color: #e74c3c; color: #fff; }
        .btn-danger:hover { background: #c0392b; color: #fff; }
        .btn-info { background: #3498db; border-color: #3498db; color: #fff; }
        .btn-info:hover { background: #2980b9; color: #fff; }
        .table-dark thead th { background: #2c3e50; color: #fff; border-color: #2c3e50; }
        .form-horizontal .form-group { margin-bottom: 1rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
        .form-horizontal .form-group label { width: 140px; flex-shrink: 0; }
        .form-horizontal .form-group input, .form-horizontal .form-group select { flex: 1; min-width: 180px; padding: 0.5rem; border: 1px solid #ecf0f1; border-radius: 4px; }
        .form-horizontal .form-actions { margin-top: 1rem; padding-left: 140px; }
        .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .row .col { padding: 0 15px; flex: 1; min-width: 0; }
        .row .col-md-3 { flex: 0 0 25%; max-width: 25%; }
        .row .col-md-6 { flex: 0 0 50%; max-width: 50%; }
        @media (max-width: 768px) { .row .col-md-3, .row .col-md-6 { flex: 0 0 100%; max-width: 100%; } }
        .auth-box { max-width: 480px; }
        .auth-box .form-horizontal .form-group label { width: 120px; }
        .auth-box input[type="text"], .auth-box input[type="email"], .auth-box input[type="password"] { padding: 0.5rem; border: 1px solid #ecf0f1; border-radius: 4px; }
        .auth-box .form-actions { padding-left: 0; }
        .nav-dropdown { position: relative; }
        .nav-dropdown-toggle { color: #fff; text-decoration: none; padding: 19px 10px; display: inline-flex; align-items: center; gap: 0.25rem; background: 0; border: 0; font: inherit; cursor: pointer; }
        .nav-dropdown-toggle:hover { color: #18bc9c; }
        .nav-dropdown-toggle::after { content: ''; border: 4px solid transparent; border-top-color: currentColor; margin-left: 2px; }
        .nav-dropdown-menu { position: absolute; top: 100%; left: 0; min-width: 180px; background: #fff; border: 1px solid #ecf0f1; border-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); list-style: none; margin: 0; padding: 4px 0; z-index: 1000; display: none; }
        .nav-dropdown:hover > .nav-dropdown-menu { display: block; }
        .nav-dropdown-menu a { color: #2c3e50; padding: 8px 16px; display: block; white-space: nowrap; }
        .nav-dropdown-menu a:hover { background: #ecf0f1; color: #18bc9c; }
        .nav-dropdown-sub { position: relative; }
        .nav-dropdown-sub .nav-dropdown-menu { left: 100%; top: 0; display: none; }
        .nav-dropdown-sub:hover .nav-dropdown-menu { display: block; }
        .nav-dropdown-sub > a::after { content: ''; float: right; border: 4px solid transparent; border-left-color: currentColor; margin-top: 4px; }
        .nav-bar .nav-dropdown-menu form { padding: 0; }
        .nav-bar .nav-dropdown-menu button { width: 100%; text-align: left; padding: 8px 16px; background: 0; border: 0; cursor: pointer; color: #2c3e50; font: inherit; }
        .nav-bar .nav-dropdown-menu button:hover { background: #ecf0f1; color: #18bc9c; }
        .nav-dropdown-menu a.nav-link-disabled { color: #95a5a6; cursor: not-allowed; pointer-events: none; text-decoration: none; padding: 8px 16px; display: block; }
        .nav-dropdown-menu a.nav-link-disabled:hover { background: transparent; color: #95a5a6; }
        .group-tabs, .viewgroups-tabs { list-style: none; margin: 0 0 1rem; padding: 0; display: flex; flex-direction: row; flex-wrap: wrap; gap: 8px; }
        .group-tabs li, .viewgroups-tabs li { margin: 0; padding: 0; flex: 0 0 auto; display: inline-block; }
        .group-tabs li a, .group-tabs li strong, .viewgroups-tabs li a, .viewgroups-tabs li strong { display: inline-block; padding: 8px 16px; background: #ecf0f1; border: 1px solid #bdc3c7; border-radius: 4px; color: #2c3e50; text-decoration: none; }
        .group-tabs li a:hover, .viewgroups-tabs li a:hover { background: #d5dbdb; }
        .group-tabs li.active strong, .viewgroups-tabs li.active strong { background: #2c3e50; color: #fff; border-color: #2c3e50; }
        /* Backend (manage) theme: lighter blue to distinguish from public */
        body.app-backend { background: #e8f4fc; }
        body.app-backend .nav-bar { background: #5d9cec; }
        body.app-backend .nav-bar a:hover,
        body.app-backend .nav-bar .nav-dropdown-toggle:hover,
        body.app-backend .nav-bar form button:hover { color: #2c3e50; }
        body.app-backend .panel-heading { background: #d5e8f7; border-color: #d5e8f7; }
        body.app-backend .group-tabs li.active strong,
        body.app-backend .viewgroups-tabs li.active strong { background: #5d9cec; border-color: #5d9cec; }
        @stack('styles')
    </style>
</head>
<body id="app-layout" class="@if(request()->routeIs('manage.*')) app-backend @endif @if(request()->routeIs('mat.*')) mat-page @endif">
    @unless(request()->routeIs('mat.*'))
    <nav class="nav-bar">
        <div class="wrap">
            <a href="{{ route('home') }}" class="brand">AutoWrestle</a>
            <a href="{{ route('home') }}">Home</a>
            @if(isset($manageNav) && $manageNav && isset($tournament))
                {{-- Tournament manage nav: dropdowns for Tournament, View, Bracket, Bout, Print --}}
                <span class="nav-dropdown">
                    <span class="nav-dropdown-toggle">Tournament</span>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-sub">
                            <a href="#">Print Check-in Sheet</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    <li><a href="{{ route('manage.checkin.print', [$tournament->id, $d->id]) }}" target="_blank">{{ $d->DivisionName }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        <li><a href="{{ route('manage.scansheet.print', $tournament->id) }}" target="_blank">Print Scan Sheet</a></li>
                        <li><a href="{{ route('manage.checkin.index', $tournament->id) }}">Check-in</a></li>
                        <li class="nav-dropdown-sub">
                            <a href="#">Remove No Shows</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    <li><a href="{{ route('manage.checkin.clearUncheckedDivision', [$tournament->id, $d->id]) }}" onclick="return confirm('Remove all wrestlers who are not checked in from {{ $d->DivisionName }}? They will be removed from the tournament before bouting.');">{{ $d->DivisionName }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        <li><a href="{{ route('manage.tournaments.edit', $tournament->id) }}">Edit Info</a></li>
                        <li><a href="{{ route('manage.divisions.index', $tournament->id) }}">Edit Divisions</a></li>
                    </ul>
                </span>
                <span class="nav-dropdown">
                    <span class="nav-dropdown-toggle">View</span>
                    <ul class="nav-dropdown-menu">
                        <li><a href="{{ route('manage.view.summary', $tournament->id) }}">Summary</a></li>
                        <li><a href="{{ route('manage.viewgroups.index', $tournament->id) }}">View Groups</a></li>
                        <li><a href="{{ route('manage.tournaments.show', $tournament->id) }}">Match Board</a></li>
                        <li><a href="{{ route('manage.projection.index', $tournament->id) }}">Coming up / Projection</a></li>
                    </ul>
                </span>
                <span class="nav-dropdown">
                    <span class="nav-dropdown-toggle">Bracket</span>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-sub">
                            <a href="#">Bracket Division</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    <li><a href="{{ route('manage.brackets.create', [$tournament->id, $d->id]) }}" onclick="return confirm('Create brackets for {{ $d->DivisionName }}?');">{{ $d->DivisionName }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="nav-dropdown-sub">
                            <a href="#">View Brackets</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    @foreach($d->divGroups as $g)
                                        <li>
                                            @if($g->bracketed)
                                                <a href="{{ route('manage.brackets.show', [$tournament->id, $g->id]) }}">{{ $g->Name }}</a>
                                            @else
                                                <a href="#" class="nav-link-disabled" onclick="return false;">{{ $g->Name }} – Not bracketed</a>
                                            @endif
                                        </li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </li>
                        <li class="nav-dropdown-sub">
                            <a href="#">Print Brackets</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    <li>
                                        @if($d->bouted)
                                            <a href="{{ route('manage.brackets.show', [$tournament->id, $d->divGroups->first()?->id]) }}" target="_blank">{{ $d->DivisionName }}</a>
                                        @else
                                            <a href="#" class="nav-link-disabled" onclick="return false;">{{ $d->DivisionName }} – Not bouted</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="nav-dropdown-sub">
                            <a href="#">Un-Bracket Division</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    <li>
                                        @if($d->Bracketed)
                                            <a href="{{ route('manage.brackets.unbracket', [$tournament->id, $d->id]) }}" onclick="return confirm('Un-bracket {{ $d->DivisionName }}?');">{{ $d->DivisionName }}</a>
                                        @else
                                            <a href="#" class="nav-link-disabled" onclick="return false;">{{ $d->DivisionName }} – Not bracketed</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </span>
                <span class="nav-dropdown">
                    <span class="nav-dropdown-toggle">Bout</span>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-sub">
                            <a href="#">Bout Division</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    @if($d->Bracketed)
                                        <li><a href="{{ route('manage.bouts.create', [$tournament->id, $d->id]) }}" onclick="return confirm('Create bouts for {{ $d->DivisionName }}?');">{{ $d->DivisionName }}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                        <li><a href="{{ route('manage.bouts.selectPrint', $tournament->id) }}">Print Bouts</a></li>
                        <li><a href="{{ route('manage.mats.index', $tournament->id) }}">Change mat</a></li>
                        <li class="nav-dropdown-sub">
                            <a href="#">Un-Bout Division</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    @if($d->bouted)
                                        <li><a href="{{ route('manage.bouts.unbout', [$tournament->id, $d->id]) }}" onclick="return confirm('Un-bout {{ $d->DivisionName }}?');">{{ $d->DivisionName }}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </span>
                <span class="nav-dropdown">
                    <span class="nav-dropdown-toggle">Print</span>
                    <ul class="nav-dropdown-menu">
                        <li class="nav-dropdown-sub">
                            <a href="#">Print Brackets</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    @if($d->bouted)
                                        <li><a href="{{ route('manage.brackets.show', [$tournament->id, $d->divGroups->first()?->id]) }}" target="_blank">{{ $d->DivisionName }}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                        <li class="nav-dropdown-sub">
                            <a href="#">Print Bouts</a>
                            <ul class="nav-dropdown-menu">
                                <li><a href="{{ route('manage.bouts.selectPrint', $tournament->id) }}">By Division</a></li>
                            </ul>
                        </li>
                        <li class="nav-dropdown-sub">
                            <a href="#">Print Check-in Sheet</a>
                            <ul class="nav-dropdown-menu">
                                @foreach($tournament->divisions as $d)
                                    <li><a href="{{ route('manage.checkin.print', [$tournament->id, $d->id]) }}" target="_blank">{{ $d->DivisionName }}</a></li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </span>
            @else
                <a href="{{ route('tournaments.list') }}">Tournaments</a>
                @auth
                    @if(auth()->user()->isScorer())
                        <a href="{{ route('mat.dashboard') }}">Match list</a>
                        <a href="{{ route('mat.settings') }}">Settings</a>
                    @else
                        <a href="{{ route('wrestlers.index') }}">My Wrestlers</a>
                        <a href="{{ route('manage.tournaments.index') }}">Manage</a>
                    @endif
                @endauth
            @endif
            <span class="spacer"></span>
            @guest
                <a href="{{ route('login') }}">Login</a>
                @if(Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @else
                <span class="nav-dropdown" style="margin-left: auto;">
                    <span class="nav-dropdown-toggle">{{ auth()->user()->name }}</span>
                    <ul class="nav-dropdown-menu" style="right: 0; left: auto;">
                        @if(auth()->user()->isScorer())
                            <li><a href="{{ route('mat.dashboard') }}">Match list</a></li>
                            <li><a href="{{ route('mat.settings') }}">Settings</a></li>
                            <li><a href="{{ route('password.change') }}">Change password</a></li>
                            <li><a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                        @else
                            <li><a href="{{ route('password.change') }}">Change password</a></li>
                            <li><a href="{{ route('wrestlers.index') }}">Manage Wrestlers</a></li>
                            <li><a href="{{ route('manage.tournaments.index') }}">Manage a Tournament</a></li>
                            @if(auth()->user()->isAdmin())
                            <li><a href="{{ route('manage.scorers.index') }}">Scorer users</a></li>
                            @endif
                            <li><a href="#">Add New Team</a></li>
                            <li><a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                        @endif
                    </ul>
                </span>
                <form id="logout-form" action="{{ url('/logout') }}" method="post" style="display: none;">@csrf</form>
            @endguest
        </div>
    </nav>
    @endunless
    <div class="main" @if(request()->routeIs('mat.*')) style="max-width: none; padding: 0;" @endif>
        @if(request()->routeIs('mat.*'))
            @yield('content')
        @else
        <div class="container">
            @if(session('success'))
                <p class="success">{{ session('success') }}</p>
            @endif
            @if(session('error'))
                <p class="error">{{ session('error') }}</p>
            @endif
            @hasSection('panel_title')
                <div class="panel panel-default">
                    <div class="panel-heading">@yield('panel_title')</div>
                    <div class="panel-body">@yield('content')</div>
                </div>
            @else
                @yield('content')
            @endif
        </div>
        @endif
    </div>
    @if(request()->routeIs('mat.*'))
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
    @endif
</body>
</html>
