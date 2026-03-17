<nav style="margin-bottom: 1rem; padding: 0.5rem 0; border-bottom: 1px solid #ddd;">
    <a href="{{ route('mat.dashboard') }}">Match list</a>
    @if(!empty($boutId))
        | <a href="{{ route('mat.bout.show', ['boutId' => $boutId]) }}">Scoring</a>
        | <a href="{{ route('mat.bout.history', ['boutId' => $boutId]) }}">Summary</a>
        | <a href="{{ route('mat.bout.results', ['boutId' => $boutId]) }}" @if(!empty($current) && $current === 'results') style="font-weight: 700;" @endif>Results</a>
    @endif
    | <a href="{{ route('mat.virtual') }}" id="mat-virtual-link" data-virtual-url="{{ route('mat.virtual') }}">Virtual</a>
    | <a href="{{ route('mat.settings') }}">Settings</a>
    | <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('mat-logout-form').submit();">Logout</a>
    <form id="mat-logout-form" action="{{ url('/logout') }}" method="post" style="display: none;">@csrf</form>
</nav>
