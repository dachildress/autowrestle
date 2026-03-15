<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <title>@yield('title', 'AutoWrestle') - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/blade.css'])
    @stack('styles')
</head>
<body id="app-layout" class="@if(request()->routeIs('manage.*')) app-backend @endif">
    <nav class="nav-bar">
        <div class="wrap">
            <a href="{{ route('home') }}" class="brand">
            @if(function_exists('site_content_image') && site_content_image('site.logo'))
                <img src="{{ site_content_image('site.logo') }}" alt="{{ config('app.name') }}" class="h-8 max-h-[40px] w-auto">
            @else
                AutoWrestle
            @endif
            </a>
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
                        <li><a href="{{ route('manage.tournaments.users', $tournament->id) }}">Tournament access</a></li>
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
                <span class="nav-dropdown">
                    <span class="nav-dropdown-toggle">Reports</span>
                    <ul class="nav-dropdown-menu">
                        <li><a href="{{ route('manage.reports.index', $tournament->id) }}">Reports home</a></li>
                        <li><a href="{{ route('manage.reports.completed', $tournament->id) }}">Completed Brackets</a></li>
                        <li><a href="{{ route('manage.reports.groups', $tournament->id) }}">Group Results</a></li>
                        <li><a href="{{ route('manage.reports.brackets', $tournament->id) }}">Bracket Results</a></li>
                        <li><a href="{{ route('manage.reports.wrestlers', $tournament->id) }}">Wrestler Results</a></li>
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
                            <li><a href="{{ route('manage.content.index') }}">Site content</a></li>
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
    <div class="main">
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
    </div>
    <footer class="mt-12 border-t border-slate-200 bg-white py-6">
        <div class="container text-center text-sm text-slate-500">
            {{ content('footer.text') }}
        </div>
    </footer>
</body>
</html>
