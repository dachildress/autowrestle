@extends('layouts.autowrestle')

@section('title', $tournament->TournamentName)

@section('content')
<div class="mb-6">
    {{-- Header: title, date, status --}}
    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $tournament->TournamentName }}</h1>
    @php
        $startDate = $tournament->TournamentDate->format('M j, Y');
        $endDate = $tournament->end_date ? $tournament->end_date->format('M j, Y') : null;
        $dateRange = $endDate && $endDate !== $startDate ? $startDate . ' – ' . $endDate : $startDate;
    @endphp
    <p class="mt-1 text-slate-600">{{ $dateRange }}</p>
    <div class="mt-2">
        @if($tournament->isPast() || $registrationLocked)
            <x-badge variant="default">COMPLETED</x-badge>
        @elseif($registrationOpen)
            <x-badge variant="success">Registration open</x-badge>
        @else
            <x-badge variant="warning">Registration closed</x-badge>
        @endif
    </div>
</div>

<div class="flex flex-col gap-8 lg:flex-row">
    {{-- Left sidebar nav --}}
    <nav class="lg:w-56 shrink-0" aria-label="Tournament sections">
        <ul class="space-y-0 border border-slate-200 rounded-lg bg-white shadow-sm overflow-hidden">
            @php
                $navItems = [
                    'information' => 'Information',
                    'my-wrestlers' => 'My Wrestlers',
                    'brackets' => 'Brackets',
                    'teams' => 'Teams',
                    'results' => 'Results',
                ];
                if (($tournament->enable_challenge_matches ?? false)) {
                    $navItems['challenge-matches'] = 'Challenge Matches';
                }
                if (($tournament->enable_challenge_matches ?? false) && auth()->check()) {
                    $navItems['challenge-match'] = 'Challenge Match';
                }
            @endphp
            @foreach($navItems as $tabKey => $label)
                <li>
                    @php
                        $isChallenge = ($tabKey === 'challenge-match');
                        $navHref = $isChallenge
                            ? (function () use ($tournament) {
                                $userId = auth()->id();
                                $incomingQuery = \App\Models\ChallengeRequest::where('tournament_id', $tournament->id)
                                    ->where('challenged_user_id', $userId)
                                    ->where('status', \App\Models\ChallengeRequest::STATUS_PENDING_ACCEPTANCE);
                                $incomingCount = (clone $incomingQuery)->count();
                                if ($incomingCount > 0) {
                                    if ($incomingCount === 1) {
                                        $incomingId = (clone $incomingQuery)->orderByDesc('created_at')->value('id');
                                        return route('challenge.show', [$tournament->id, $incomingId]);
                                    }
                                    return route('challenge.index', $tournament->id);
                                }
                                return route('challenge.create', $tournament->id);
                            })()
                            : route('tournaments.show', ['id' => $tournament->id, 'tab' => $tabKey]);
                        $isActive = $isChallenge ? false : ($tab === $tabKey);
                    @endphp
                    <a href="{{ $navHref }}"
                       class="block px-4 py-3 no-underline border-b border-slate-200 last:border-b-0 {{ $isActive ? 'bg-slate-100 text-slate-900 font-semibold border-l-4 border-l-aw-accent' : 'text-slate-900 hover:bg-slate-50' }}">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Main content --}}
    <div class="min-w-0 flex-1">
        @if($tab === 'information')
            {{-- Information tab --}}
            <div class="space-y-6">
                {{-- Registration banner: dark when closed, light with dark text when open --}}
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg px-4 py-3 {{ $registrationLocked || !$registrationOpen ? 'bg-slate-700 text-white' : 'bg-slate-100 border border-slate-200 text-slate-900' }}">
                    <span class="inline-flex items-center gap-2">
                        @if($registrationLocked || !$registrationOpen)
                            <svg class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" /></svg>
                            <span>Registration is closed</span>
                        @else
                            <span class="font-medium">Registration is open</span>
                        @endif
                    </span>
                    @auth
                        @if($registrationLocked)
                            <x-button href="{{ route('tournaments.register.locked') }}" variant="ghost" class="{{ $registrationLocked || !$registrationOpen ? '!text-white hover:!bg-slate-600' : '' }}">Registration</x-button>
                        @else
                            <x-button href="{{ route('tournaments.register', $tournament->id) }}" variant="ghost" class="{{ $registrationLocked || !$registrationOpen ? '!text-white hover:!bg-slate-600' : '' }} inline-flex items-center gap-1">
                                Registration
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </x-button>
                        @endif
                    @else
                        <x-button href="{{ route('login') }}" variant="ghost" class="{{ $registrationLocked || !$registrationOpen ? '!text-white hover:!bg-slate-600' : '' }} inline-flex items-center gap-1">
                            Registration
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </x-button>
                    @endauth
                </div>

                {{-- Tournament overview card --}}
                @php $flyerIsImage = $tournament->link && !\Illuminate\Support\Str::endsWith(strtolower($tournament->link), '.pdf'); @endphp
                <x-card :padding="true">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        @if($tournament->link)
                            <div class="shrink-0">
                                @if($flyerIsImage)
                                    <img src="{{ asset('flyers/' . $tournament->link) }}" alt="" class="h-16 w-16 rounded object-cover" loading="lazy">
                                @else
                                    <a href="{{ asset('flyers/' . $tournament->link) }}" target="_blank" rel="noopener" class="flex h-16 w-16 items-center justify-center rounded border border-slate-200 bg-slate-100 text-slate-500 hover:bg-slate-200" title="Event flyer (PDF)">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    </a>
                                @endif
                            </div>
                        @endif
                        <div class="min-w-0 flex-1 space-y-3">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $tournament->TournamentName }}</h2>
                            <dl class="grid gap-2 text-sm">
                                <div>
                                    <dt class="font-medium text-slate-500">Start date</dt>
                                    <dd class="text-slate-900">{{ $tournament->TournamentDate->format('D, M j, Y') }}</dd>
                                </div>
                                @if($tournament->end_date)
                                    <div>
                                        <dt class="font-medium text-slate-500">End date</dt>
                                        <dd class="text-slate-900">{{ $tournament->end_date->format('D, M j, Y') }}</dd>
                                    </div>
                                @endif
                                @if($tournament->location_name || $tournament->location_address)
                                    <div>
                                        <dt class="font-medium text-slate-500">Location</dt>
                                        <dd class="text-slate-900 inline-flex items-start gap-2">
                                            <svg class="h-4 w-4 mt-0.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            <span>{{ $tournament->location_name }}{{ $tournament->location_name && $tournament->location_address ? ' – ' : '' }}{{ $tournament->location_address }}</span>
                                        </dd>
                                    </div>
                                @endif
                                @if($tournament->contact_name || $tournament->contact_email)
                                    <div class="pt-2 border-t border-slate-200">
                                        <dt class="font-medium text-slate-500">TOURNAMENT CONTACT</dt>
                                        <dd class="text-slate-900 mt-1">
                                            @if($tournament->contact_name){{ $tournament->contact_name }}<br>@endif
                                            @if($tournament->contact_email)<a href="mailto:{{ $tournament->contact_email }}" class="text-aw-accent hover:underline">{{ $tournament->contact_email }}</a>@endif
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </x-card>

                {{-- Event flyer and info --}}
                <section>
                    <h2 class="mb-3 text-xl font-semibold text-slate-900">Event Flyer and Info</h2>
                    <div class="flex flex-col gap-4 sm:flex-row">
                        @if($tournament->link)
                            <div class="shrink-0">
                                @if($flyerIsImage)
                                    <a href="{{ asset('flyers/' . $tournament->link) }}" target="_blank" rel="noopener" class="block">
                                        <img src="{{ asset('flyers/' . $tournament->link) }}" alt="Event flyer" class="max-w-xs rounded-lg border border-slate-200 shadow-sm" loading="lazy">
                                    </a>
                                @else
                                    <a href="{{ asset('flyers/' . $tournament->link) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-slate-700 hover:bg-slate-100">
                                        <svg class="h-10 w-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        <span class="font-medium">Event flyer</span>
                                        <span class="text-sm text-slate-500">(PDF)</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                        <div class="min-w-0 flex-1 text-slate-700">
                            @if($tournament->message)
                                <div class="whitespace-pre-wrap">{{ $tournament->message }}</div>
                            @elseif($tournament->link)
                                <p><a href="{{ asset('flyers/' . $tournament->link) }}" target="_blank" rel="noopener" class="text-aw-accent hover:underline">View tournament flyer</a></p>
                            @else
                                <p class="text-slate-500">No additional details.</p>
                            @endif
                        </div>
                    </div>
                </section>

                @if($tournament->divisions->isNotEmpty())
                    <section>
                        <h2 class="mb-2 text-lg font-semibold text-slate-900">Divisions</h2>
                        <ul class="list-disc list-inside text-slate-700">
                            @foreach($tournament->divisions as $div)
                                <li>{{ $div->DivisionName }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

        @elseif($tab === 'my-wrestlers')
            {{-- My Wrestlers tab --}}
            <h2 class="mb-4 text-xl font-semibold text-slate-900">My Wrestlers</h2>
            @guest
                <p class="text-slate-600">Log in to see your wrestlers and register them for this tournament.</p>
                <p class="mt-3"><x-button href="{{ route('login') }}" variant="secondary">Log in</x-button></p>
            @else
                @if($userWrestlers === null || $userWrestlers->isEmpty())
                    <p class="text-slate-600">You don’t have any wrestlers yet. Add wrestlers in My Wrestlers to register them for tournaments.</p>
                    <p class="mt-3"><x-button href="{{ route('wrestlers.index') }}" variant="secondary">My Wrestlers</x-button></p>
                @else
                    @if($registrationLocked || !$registrationOpen)
                        <p class="text-slate-600 mb-4">Registration is closed for this tournament.</p>
                    @endif
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="min-w-full border-collapse">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Name</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Club</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Age</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Grade</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Weight</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-900">Status</th>
                                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-900">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($userWrestlers as $w)
                                    @php $status = $statusByWrestler[$w->id] ?? 'add'; @endphp
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 text-slate-900">{{ $w->full_name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $w->wr_club ?? '–' }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $w->wr_age ?? '–' }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $w->wr_grade ?? '–' }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $w->wr_weight ?? '–' }}</td>
                                        <td class="px-4 py-3">
                                            @if($status === 'locked')
                                                <x-badge variant="default">Registered</x-badge>
                                            @elseif($status === 'withdraw')
                                                <x-badge variant="success">Registered</x-badge>
                                            @else
                                                <span class="text-slate-500">Not registered</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if($status === 'locked')
                                                <span class="text-slate-400 text-sm">Locked</span>
                                            @elseif($status === 'withdraw')
                                                <a href="{{ route('tournaments.register.withdraw', ['wid' => $w->id, 'tid' => $tournament->id]) }}" class="text-aw-accent hover:underline text-sm">Withdraw</a>
                                            @else
                                                @if($registrationLocked || !$registrationOpen)
                                                    <span class="text-slate-400 text-sm">–</span>
                                                @else
                                                    <a href="{{ route('tournaments.register.add', ['wid' => $w->id, 'tid' => $tournament->id]) }}" class="text-aw-accent hover:underline text-sm">Add</a>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endguest
        @elseif($tab === 'brackets')
            <h2 class="mb-4 text-xl font-semibold text-slate-900">Brackets</h2>

            @if(empty($selectedBracketsData))
                {{-- Prompt to select brackets --}}
                <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-200 bg-slate-50/50 py-16 px-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="mt-4 text-slate-600">Select at least one division and weight class to open a bracket.</p>
                    <button type="button" onclick="document.getElementById('brackets-modal').classList.remove('hidden')" class="mt-6 inline-flex items-center justify-center rounded-md bg-aw-accent px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-aw-accent focus:ring-offset-2">
                        Select Brackets
                    </button>
                </div>

                {{-- Modal: division + bracket checkboxes --}}
                <div id="brackets-modal" class="fixed inset-0 z-50 hidden" aria-modal="true" aria-labelledby="brackets-modal-title">
                    <div class="fixed inset-0 bg-slate-600/60" onclick="document.getElementById('brackets-modal').classList.add('hidden')"></div>
                    <div class="fixed right-0 top-0 bottom-0 w-full max-w-md overflow-hidden bg-white shadow-xl flex flex-col">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                            <h3 id="brackets-modal-title" class="text-lg font-semibold text-slate-900">Select brackets</h3>
                            <button type="button" onclick="document.getElementById('brackets-modal').classList.add('hidden')" class="rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700" aria-label="Close">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form method="get" action="{{ route('tournaments.show', ['id' => $tournament->id, 'tab' => 'brackets']) }}" id="brackets-form" class="flex flex-1 flex-col overflow-hidden">
                            <input type="hidden" name="tab" value="brackets">
                            <div class="flex-1 overflow-y-auto px-4 py-3">
                                @forelse(($bracketOptionsByDivision ?? []) as $divIndex => $division)
                                    <div class="mb-3 border border-slate-200 rounded-lg overflow-hidden">
                                        <button type="button" class="bracket-division-toggle w-full flex items-center justify-between px-3 py-2.5 text-left font-medium text-slate-700 bg-slate-50 hover:bg-slate-100 border-b border-slate-200" aria-expanded="true" data-target="bracket-div-{{ $divIndex }}">
                                            <span>{{ $division['division_name'] }}</span>
                                            <svg class="bracket-division-chevron h-5 w-5 text-slate-500 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div id="bracket-div-{{ $divIndex }}" class="bracket-division-content px-3 py-2">
                                            <ul class="space-y-1.5">
                                                @foreach($division['brackets'] as $opt)
                                                    @php
                                                        $wMin = $opt['weight_min'] ?? null;
                                                        $wMax = $opt['weight_max'] ?? null;
                                                        $weightLabel = ($wMin !== null && $wMax !== null && $wMin > 0) ? ($wMin === $wMax ? (string)$wMin : $wMin . ' – ' . $wMax) : ($opt['group_name'] ?? 'Bracket ' . $opt['bracket_id']);
                                                    @endphp
                                                    <li class="flex items-center gap-2">
                                                        <input type="checkbox" name="brackets[]" value="{{ $opt['bracket_id'] }}" id="bracket-{{ $opt['bracket_id'] }}" class="h-4 w-4 rounded border-slate-300 text-aw-accent focus:ring-aw-accent">
                                                        <label for="bracket-{{ $opt['bracket_id'] }}" class="text-sm text-slate-900">{{ $weightLabel }}</label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-slate-600">No brackets yet. Brackets will appear here once divisions and weight classes have been created and bouted.</p>
                                @endforelse
                                @if(!empty($bracketOptionsByDivision))
                                    <div class="mt-4 border-t border-slate-200 pt-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" id="brackets-select-all" class="h-4 w-4 rounded border-slate-300 text-aw-accent focus:ring-aw-accent">
                                            <span class="text-sm font-medium text-slate-700">Select all brackets</span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                                <button type="button" onclick="document.getElementById('brackets-modal').classList.add('hidden')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</button>
                                <button type="submit" name="open" value="1" class="rounded-md bg-aw-accent px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90">Open</button>
                            </div>
                        </form>
                    </div>
                </div>
                @if(!empty($bracketOptionsByDivision))
                <script>
                    (function() {
                        var form = document.getElementById('brackets-form');
                        var selectAll = document.getElementById('brackets-select-all');
                        if (selectAll && form) {
                            selectAll.addEventListener('change', function() {
                                form.querySelectorAll('input[name="brackets[]"]').forEach(function(cb) { cb.checked = selectAll.checked; });
                            });
                        }
                        document.querySelectorAll('.bracket-division-toggle').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var targetId = this.getAttribute('data-target');
                                var content = document.getElementById(targetId);
                                var chevron = this.querySelector('.bracket-division-chevron');
                                var expanded = this.getAttribute('aria-expanded') === 'true';
                                if (content) { content.classList.toggle('hidden', expanded); }
                                if (chevron) { chevron.style.transform = expanded ? 'rotate(-90deg)' : 'rotate(0)'; }
                                this.setAttribute('aria-expanded', !expanded);
                            });
                        });
                    })();
                </script>
                @endif
            @else
                {{-- Selected brackets: tabs + bout cards --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @foreach($selectedBracketsData as $index => $bracketData)
                        @php
                            $m = $bracketData->meta;
                            $chipWeight = (isset($m['weight_min']) && isset($m['weight_max']) && $m['weight_min'] > 0) ? ($m['weight_min'] === $m['weight_max'] ? (string)$m['weight_min'] : $m['weight_min'] . ' – ' . $m['weight_max']) : ($m['group_name'] ?? 'Bracket');
                        @endphp
                        <div class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-3 py-1.5 text-sm">
                            <span class="font-medium text-slate-900">{{ $chipWeight }} – {{ $m['division_name'] ?? '' }}</span>
                            <a href="{{ route('tournaments.show', ['id' => $tournament->id, 'tab' => 'brackets', 'brackets' => array_values(array_diff($selectedBracketIds, [$bracketData->bracket_id]))]) }}" class="ml-1 text-slate-400 hover:text-slate-600" aria-label="Remove bracket">×</a>
                        </div>
                    @endforeach
                    <button type="button" onclick="document.getElementById('brackets-modal').classList.remove('hidden')" class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-600 hover:bg-slate-50" aria-label="Add bracket">+</button>
                </div>

                {{-- Re-open modal for adding more (hidden when we have selections) --}}
                <div id="brackets-modal" class="fixed inset-0 z-50 hidden" aria-modal="true">
                    <div class="fixed inset-0 bg-slate-600/60" onclick="document.getElementById('brackets-modal').classList.add('hidden')"></div>
                    <div class="fixed right-0 top-0 bottom-0 w-full max-w-md overflow-hidden bg-white shadow-xl flex flex-col">
                        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                            <h3 class="text-lg font-semibold text-slate-900">Add brackets</h3>
                            <button type="button" onclick="document.getElementById('brackets-modal').classList.add('hidden')" class="rounded p-1 text-slate-500 hover:bg-slate-100">×</button>
                        </div>
                        <form method="get" action="{{ route('tournaments.show', ['id' => $tournament->id, 'tab' => 'brackets']) }}" class="flex flex-1 flex-col overflow-hidden">
                            <input type="hidden" name="tab" value="brackets">
                            @foreach($selectedBracketIds as $bid)
                                <input type="hidden" name="brackets[]" value="{{ $bid }}">
                            @endforeach
                            <div class="flex-1 overflow-y-auto px-4 py-3">
                                @foreach(($bracketOptionsByDivision ?? []) as $addDivIndex => $division)
                                    <div class="mb-3 border border-slate-200 rounded-lg overflow-hidden">
                                        <button type="button" class="add-bracket-division-toggle w-full flex items-center justify-between px-3 py-2.5 text-left font-medium text-slate-700 bg-slate-50 hover:bg-slate-100 border-b border-slate-200" aria-expanded="true" data-target="add-bracket-div-{{ $addDivIndex }}">
                                            <span>{{ $division['division_name'] }}</span>
                                            <svg class="add-bracket-division-chevron h-5 w-5 text-slate-500 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div id="add-bracket-div-{{ $addDivIndex }}" class="add-bracket-division-content px-3 py-2">
                                            <ul class="space-y-1.5">
                                                @foreach($division['brackets'] as $opt)
                                                    @php
                                                        $addWMin = $opt['weight_min'] ?? null;
                                                        $addWMax = $opt['weight_max'] ?? null;
                                                        $addWeightLabel = ($addWMin !== null && $addWMax !== null && $addWMin > 0) ? ($addWMin === $addWMax ? (string)$addWMin : $addWMin . ' – ' . $addWMax) : ($opt['group_name'] ?? 'Bracket ' . $opt['bracket_id']);
                                                        $already = in_array($opt['bracket_id'], $selectedBracketIds);
                                                    @endphp
                                                    <li class="flex items-center gap-2">
                                                        <input type="checkbox" name="brackets[]" value="{{ $opt['bracket_id'] }}" id="add-bracket-{{ $opt['bracket_id'] }}" class="h-4 w-4 rounded border-slate-300 text-aw-accent" {{ $already ? 'disabled' : '' }}>
                                                        <label for="add-bracket-{{ $opt['bracket_id'] }}" class="text-sm {{ $already ? 'text-slate-400' : 'text-slate-900' }}">{{ $addWeightLabel }}{{ $already ? ' (already added)' : '' }}</label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-end gap-2 border-t border-slate-200 px-4 py-3">
                                <button type="button" onclick="document.getElementById('brackets-modal').classList.add('hidden')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancel</button>
                                <button type="submit" class="rounded-md bg-aw-accent px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Add</button>
                            </div>
                        </form>
                    </div>
                </div>
                <script>
                    (function() {
                        document.querySelectorAll('.add-bracket-division-toggle').forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                var targetId = this.getAttribute('data-target');
                                var content = document.getElementById(targetId);
                                var chevron = this.querySelector('.add-bracket-division-chevron');
                                var expanded = this.getAttribute('aria-expanded') === 'true';
                                if (content) { content.classList.toggle('hidden', expanded); }
                                if (chevron) { chevron.style.transform = expanded ? 'rotate(-90deg)' : 'rotate(0)'; }
                                this.setAttribute('aria-expanded', !expanded);
                            });
                        });
                    })();
                </script>

                {{-- Bout cards for each selected bracket --}}
                @foreach($selectedBracketsData as $bracketData)
                    @php
                        $headMeta = $bracketData->meta;
                        $headWeight = (isset($headMeta['weight_min']) && isset($headMeta['weight_max']) && $headMeta['weight_min'] > 0) ? ($headMeta['weight_min'] === $headMeta['weight_max'] ? (string)$headMeta['weight_min'] : $headMeta['weight_min'] . ' – ' . $headMeta['weight_max']) : ($headMeta['group_name'] ?? 'Bracket');
                    @endphp
                    <div class="mb-8">
                        <h3 class="mb-3 text-base font-semibold text-slate-900">{{ $headWeight }} – {{ $headMeta['division_name'] ?? '' }}</h3>
                        @if(empty($bracketData->bouts))
                            <p class="text-slate-600">No bout data for this bracket.</p>
                        @else
                            @php
                                $boutsByRound = collect($bracketData->bouts)->groupBy('round');
                                $rounds = $boutsByRound->keys()->sort()->values()->all();
                            @endphp
                            <div class="flex flex-wrap gap-6 overflow-x-auto pb-2">
                                @foreach($rounds as $roundNum)
                                    @php $roundBouts = $boutsByRound->get($roundNum, []); @endphp
                                    <div class="flex flex-col gap-3 shrink-0" style="min-width: 12rem;">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Round {{ $roundNum }}</p>
                                        @foreach($roundBouts as $bout)
                                            @php
                                                $winnerId = $bout['winner_id'] ?? null;
                                                $redWins = $winnerId !== null && $winnerId == ($bout['red_wrestler_id'] ?? null);
                                                $greenWins = $winnerId !== null && $winnerId == ($bout['green_wrestler_id'] ?? null);
                                                $resultTime = trim(($bout['result_label'] ?? '') . ' ' . ($bout['time_display'] ?? ''));
                                                $boutCompleted = $winnerId !== null;
                                            @endphp
                                            <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden {{ $boutCompleted ? 'bout-card-clickable cursor-pointer hover:border-aw-accent hover:shadow-md transition-shadow' : '' }}"
                                                @if($boutCompleted) role="button" tabindex="0" data-tournament-id="{{ $tournament->id }}" data-bout-id="{{ $bout['bout_id'] }}" aria-label="View bout #{{ $bout['bout_number'] ?? $bout['bout_id'] }} details" @endif>
                                                <div class="flex justify-between items-center px-3 py-1.5 border-b border-slate-100 bg-slate-50/50 text-sm">
                                                    <span class="font-mono font-medium text-slate-700">#{{ $bout['bout_number'] ?? $bout['bout_id'] }}</span>
                                                    <span class="font-medium text-slate-600">{{ $resultTime ?: '—' }}</span>
                                                </div>
                                                <div class="px-3 py-2 space-y-1 text-sm">
                                                    <div class="flex justify-between items-center gap-2 {{ $redWins ? 'font-bold text-slate-900 border-r-4 border-slate-900 pr-2' : 'text-slate-700' }}">
                                                        <span class="min-w-0 truncate">{{ $bout['red_name'] }}</span>
                                                        @if(!empty($bout['red_club'] ?? ''))
                                                            <span class="shrink-0 text-slate-500 text-xs">{{ $bout['red_club'] }}</span>
                                                        @endif
                                                        <span class="shrink-0 tabular-nums">{{ $bout['red_score'] ?? 0 }}</span>
                                                    </div>
                                                    <div class="flex justify-between items-center gap-2 {{ $greenWins ? 'font-bold text-slate-900 border-r-4 border-slate-900 pr-2' : 'text-slate-700' }}">
                                                        <span class="min-w-0 truncate">{{ $bout['green_name'] }}</span>
                                                        @if(!empty($bout['green_club'] ?? ''))
                                                            <span class="shrink-0 text-slate-500 text-xs">{{ $bout['green_club'] }}</span>
                                                        @endif
                                                        <span class="shrink-0 tabular-nums">{{ $bout['green_score'] ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                {{-- Side panel for bout detail (opened when a completed bout card is clicked) --}}
                <div id="bout-detail-panel" class="fixed inset-y-0 right-0 z-50 hidden w-full max-w-md flex-col bg-white shadow-xl" aria-hidden="true">
                    <div id="bout-detail-panel-inner" class="flex flex-col h-full overflow-hidden p-4">
                        <div class="flex items-center justify-center py-12 text-slate-500" id="bout-detail-loading">Loading…</div>
                        <div id="bout-detail-content" class="hidden flex-1 flex flex-col overflow-hidden"></div>
                    </div>
                </div>
                <div id="bout-detail-backdrop" class="fixed inset-0 z-40 hidden bg-slate-900/30" aria-hidden="true"></div>
                <script>
                    (function() {
                        var panel = document.getElementById('bout-detail-panel');
                        var backdrop = document.getElementById('bout-detail-backdrop');
                        var contentEl = document.getElementById('bout-detail-content');
                        var loadingEl = document.getElementById('bout-detail-loading');
                        var baseUrl = '{{ url('/tournaments/' . $tournament->id . '/bout-detail') }}';

                        function openPanel() {
                            panel.classList.remove('hidden');
                            panel.setAttribute('aria-hidden', 'false');
                            backdrop.classList.remove('hidden');
                            backdrop.setAttribute('aria-hidden', 'false');
                        }
                        function closePanel() {
                            panel.classList.add('hidden');
                            panel.setAttribute('aria-hidden', 'true');
                            backdrop.classList.add('hidden');
                            backdrop.setAttribute('aria-hidden', 'true');
                        }

                        document.addEventListener('click', function(e) {
                            var card = e.target.closest('.bout-card-clickable');
                            if (card) {
                                e.preventDefault();
                                var tid = card.getAttribute('data-tournament-id');
                                var bid = card.getAttribute('data-bout-id');
                                if (!tid || !bid) return;
                                contentEl.classList.add('hidden');
                                contentEl.innerHTML = '';
                                loadingEl.classList.remove('hidden');
                                openPanel();
                                fetch(baseUrl + '/' + bid, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
                                    .then(function(r) { return r.text(); })
                                        .then(function(html) {
                                        loadingEl.classList.add('hidden');
                                        contentEl.innerHTML = html;
                                        contentEl.classList.remove('hidden');
                                    })
                                    .catch(function() {
                                        loadingEl.textContent = 'Could not load bout details.';
                                    });
                                return;
                            }
                            if (e.target.closest('.bout-panel-close') || e.target === backdrop) {
                                closePanel();
                            }
                        });
                        backdrop.addEventListener('click', closePanel);
                        document.addEventListener('keydown', function(e) {
                            if (e.key === 'Escape' && panel && !panel.classList.contains('hidden')) closePanel();
                        });
                    })();
                </script>
            @endif

        @elseif($tab === 'teams')
            <h2 class="mb-4 text-xl font-semibold text-slate-900">Teams</h2>

            <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form method="get" action="{{ route('tournaments.show', $tournament->id) }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="teams">
                    <label for="teams-division" class="sr-only">Filter by division</label>
                    <select name="division_id" id="teams-division" onchange="this.form.submit()" class="rounded-md border-slate-300 text-sm">
                        <option value="" {{ ($teamsDivisionId ?? '') === '' ? 'selected' : '' }}>All teams</option>
                        @foreach($tournament->divisions as $div)
                            <option value="{{ $div->id }}" {{ (string)($teamsDivisionId ?? '') === (string)$div->id ? 'selected' : '' }}>{{ $div->DivisionName }}</option>
                        @endforeach
                    </select>
                </form>
                <div class="relative">
                    <label for="teams-search" class="sr-only">Search teams</label>
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" id="teams-search" placeholder="Search teams" class="w-full rounded-md border-slate-300 pl-9 pr-3 py-2 text-sm sm:w-64">
                </div>
            </div>

            <p class="mb-4 text-sm font-medium text-slate-600"><span id="teams-count">{{ count($teamsData ?? []) }}</span> TEAMS</p>

            <div id="teams-list" class="space-y-1 border border-slate-200 rounded-lg bg-white overflow-hidden">
                @forelse($teamsData ?? [] as $team)
                    @php
                        $initials = strtoupper(mb_substr(preg_replace('/[^a-zA-Z]/', '', $team->club), 0, 3));
                        if ($initials === '') { $initials = '?'; }
                        $hue = abs(crc32($team->club)) % 360;
                        $badgeBg = 'hsl(' . $hue . ', 55%, 45%)';
                    @endphp
                    <div class="team-row border-b border-slate-200 last:border-b-0" data-team-name="{{ strtolower($team->club) }}">
                        <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-aw-accent" aria-expanded="false" aria-controls="team-wrestlers-{{ $loop->index }}" id="team-toggle-{{ $loop->index }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded text-xs font-semibold text-white" style="background-color: {{ $badgeBg }}">{{ $initials }}</span>
                            <span class="min-w-0 flex-1 text-slate-900">{{ $team->club }}@if($tournament->state), {{ $tournament->state }}@endif</span>
                            <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform team-chevron" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div id="team-wrestlers-{{ $loop->index }}" class="hidden border-t border-slate-100 bg-slate-50/50" aria-labelledby="team-toggle-{{ $loop->index }}">
                            <div class="overflow-x-auto px-4 py-3">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-slate-500">
                                            <th class="pb-2 pr-4 font-medium">Name</th>
                                            <th class="pb-2 px-4 font-medium text-center">Weight</th>
                                            <th class="pb-2 pl-4 font-medium text-right">Place</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($team->wrestlers as $w)
                                            <tr>
                                                <td class="py-2 pr-4 text-slate-900">{{ $w->full_name }}</td>
                                                <td class="py-2 px-4 text-center text-slate-700">{{ $w->wr_weight ?? '–' }}</td>
                                                <td class="py-2 pl-4 text-right text-slate-700">–</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-6 text-slate-600">No teams found for this tournament{{ ($teamsDivisionId ?? null) !== null && $teamsDivisionId !== '' ? ' in the selected division' : '' }}.</p>
                @endforelse
            </div>

            @if(!empty($teamsData))
            <style>
                .team-row.is-expanded .team-chevron { transform: rotate(180deg); }
            </style>
            <script>
                (function() {
                    var list = document.getElementById('teams-list');
                    if (!list) return;
                    var search = document.getElementById('teams-search');
                    var countEl = document.getElementById('teams-count');
                    var rows = list.querySelectorAll('.team-row');

                    list.addEventListener('click', function(e) {
                        var btn = e.target.closest('button[aria-controls^="team-wrestlers-"]');
                        if (!btn) return;
                        var expanded = btn.getAttribute('aria-expanded') === 'true';
                        var panelId = btn.getAttribute('aria-controls');
                        var panel = document.getElementById(panelId);
                        var row = btn.closest('.team-row');
                        if (!panel || !row) return;
                        btn.setAttribute('aria-expanded', !expanded);
                        panel.classList.toggle('hidden', expanded);
                        row.classList.toggle('is-expanded', !expanded);
                    });

                    if (search) {
                        search.addEventListener('input', function() {
                            var q = (this.value || '').trim().toLowerCase();
                            var visible = 0;
                            rows.forEach(function(row) {
                                var name = row.getAttribute('data-team-name') || '';
                                var show = !q || name.indexOf(q) !== -1;
                                row.style.display = show ? '' : 'none';
                                if (show) visible++;
                            });
                            if (countEl) countEl.textContent = visible;
                        });
                    }
                })();
            </script>
            @endif

        @elseif($tab === 'challenge-matches')
            <h2 class="mb-4 text-xl font-semibold text-slate-900">Challenge Matches</h2>

            @if(empty($challengeMatches ?? []))
                <p class="text-slate-600">No challenge matches have been scheduled yet.</p>
            @else
                <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-slate-500">
                                <th class="px-4 py-3 font-medium w-24">Bout</th>
                                <th class="px-4 py-3 font-medium w-72">Challenger</th>
                                <th class="px-4 py-3 font-medium w-72">Challenged</th>
                                <th class="px-4 py-3 font-medium w-24">Mat</th>
                                <th class="px-4 py-3 font-medium w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($challengeMatches as $m)
                                <tr class="border-b border-slate-100 last:border-b-0">
                                    <td class="px-4 py-3 font-mono text-slate-700">#{{ $m->bout_number }}</td>
                                    <td class="px-4 py-3">
                                        @php $isPin = ($m->pin ?? false) === true; @endphp
                                        @php $winnerSide = $m->pin_winner_side ?? null; @endphp
                                        @php $highlightChallenger = $isPin && $winnerSide === 'challenger'; @endphp
                                        <div class="rounded-lg border {{ $highlightChallenger ? 'border-blue-500' : 'border-slate-200' }} bg-white px-3 py-2">
                                            <div class="font-semibold text-slate-900">{{ $m->challenger_name }}</div>
                                            <div class="text-sm text-slate-500">{{ $m->challenger_club }}</div>
                                            <div class="mt-1 font-mono text-slate-900">{{ $m->challenger_display }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $isPin = ($m->pin ?? false) === true; @endphp
                                        @php $winnerSide = $m->pin_winner_side ?? null; @endphp
                                        @php $highlightChallenged = $isPin && $winnerSide === 'challenged'; @endphp
                                        <div class="rounded-lg border {{ $highlightChallenged ? 'border-blue-500' : 'border-slate-200' }} bg-white px-3 py-2">
                                            <div class="font-semibold text-slate-900">{{ $m->challenged_name }}</div>
                                            <div class="text-sm text-slate-500">{{ $m->challenged_club }}</div>
                                            <div class="mt-1 font-mono text-slate-900">{{ $m->challenged_display }}</div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">Mat {{ $m->mat_number }}</td>
                                    <td class="px-4 py-3">
                                        @php $completed = ($m->status_label ?? '') === 'Completed'; @endphp
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ $completed ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $m->status_label ?? 'Queued' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        @elseif($tab === 'results')
            <h2 class="mb-4 text-xl font-semibold text-slate-900">Results</h2>

            <form method="get" action="{{ route('tournaments.show', $tournament->id) }}" class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:flex-wrap">
                <input type="hidden" name="tab" value="results">
                <div class="flex items-center gap-2">
                    <label for="results-division" class="text-sm font-medium text-slate-700">Division</label>
                    <select name="results_division_id" id="results-division" onchange="this.form.submit()" class="rounded-md border-slate-300 text-sm">
                        <option value="">All divisions</option>
                        @foreach($tournament->divisions as $div)
                            <option value="{{ $div->id }}" {{ (string)($resultsDivisionId ?? '') === (string)$div->id ? 'selected' : '' }}>{{ $div->DivisionName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label for="results-team" class="text-sm font-medium text-slate-700">Team</label>
                    <select name="results_team" id="results-team" onchange="this.form.submit()" class="rounded-md border-slate-300 text-sm">
                        <option value="">All teams</option>
                        @foreach($teamsForResults ?? [] as $club)
                            <option value="{{ e($club) }}" {{ (string)($resultsTeam ?? '') === (string)$club ? 'selected' : '' }}>{{ $club }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <label for="results-search" class="sr-only">Search for athlete</label>
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" id="results-search" placeholder="Search for athlete" class="w-full rounded-md border-slate-300 pl-9 pr-3 py-2 text-sm sm:w-56" value="">
                </div>
            </form>

            <div id="results-accordion" class="space-y-1 border border-slate-200 rounded-lg bg-white overflow-hidden">
                @forelse($resultsBrackets ?? [] as $bracket)
                    <div class="results-bracket border-b border-slate-200 last:border-b-0" data-bracket-id="{{ $bracket->bracket_id }}">
                        <button type="button" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-aw-accent" aria-expanded="false" aria-controls="results-placers-{{ $bracket->bracket_id }}" id="results-toggle-{{ $bracket->bracket_id }}">
                            <span class="font-medium text-slate-900">{{ $bracket->group_name }}</span>
                            <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform results-chevron" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div id="results-placers-{{ $bracket->bracket_id }}" class="hidden border-t border-slate-100 bg-slate-50/50" aria-labelledby="results-toggle-{{ $bracket->bracket_id }}">
                            <div class="overflow-x-auto px-4 py-3">
                                <table class="min-w-full text-sm results-placers-table">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-left text-slate-500">
                                            <th class="pb-2 pr-4 font-medium w-20">Place</th>
                                            <th class="pb-2 px-4 font-medium">Name</th>
                                            <th class="pb-2 pl-4 font-medium text-right">Team</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($bracket->placements as $p)
                                            <tr class="results-placer-row" data-athlete-name="{{ strtolower($p['name'] ?? '') }}">
                                                <td class="py-2 pr-4">
                                                    @if(($p['place'] ?? 0) === 1)
                                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-400 text-amber-900" title="1st" aria-hidden="true">1</span>
                                                    @elseif(($p['place'] ?? 0) === 2)
                                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-300 text-slate-700" title="2nd" aria-hidden="true">2</span>
                                                    @elseif(($p['place'] ?? 0) === 3)
                                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-600 text-amber-100" title="3rd" aria-hidden="true">3</span>
                                                    @elseif(($p['place'] ?? 0) === 4)
                                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-400 text-white" title="4th" aria-hidden="true">4</span>
                                                    @else
                                                        <span class="text-slate-600">{{ $p['place'] ?? '–' }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-2 px-4 text-slate-900">{{ $p['name'] ?? '–' }}</td>
                                                <td class="py-2 pl-4 text-right text-slate-700">{{ $p['club'] ?? '–' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-6 text-slate-600">No completed bracket results yet. Results will appear here once brackets are finished.</p>
                @endforelse
            </div>

            @if(!empty($resultsBrackets))
            <style>
                .results-bracket.is-expanded .results-chevron { transform: rotate(180deg); }
            </style>
            <script>
                (function() {
                    var accordion = document.getElementById('results-accordion');
                    if (!accordion) return;
                    var search = document.getElementById('results-search');

                    accordion.addEventListener('click', function(e) {
                        var btn = e.target.closest('button[aria-controls^="results-placers-"]');
                        if (!btn) return;
                        var expanded = btn.getAttribute('aria-expanded') === 'true';
                        var panelId = btn.getAttribute('aria-controls');
                        var panel = document.getElementById(panelId);
                        var row = btn.closest('.results-bracket');
                        if (!panel || !row) return;
                        btn.setAttribute('aria-expanded', !expanded);
                        panel.classList.toggle('hidden', expanded);
                        row.classList.toggle('is-expanded', !expanded);
                    });

                    if (search) {
                        search.addEventListener('input', function() {
                            var q = (this.value || '').trim().toLowerCase();
                            accordion.querySelectorAll('.results-placer-row').forEach(function(tr) {
                                var name = tr.getAttribute('data-athlete-name') || '';
                                tr.style.display = !q || name.indexOf(q) !== -1 ? '' : 'none';
                            });
                        });
                    }
                })();
            </script>
            @endif
        @endif
    </div>
</div>

<p class="mt-8"><x-button href="{{ route('tournaments.list') }}" variant="ghost">← Back to tournaments</x-button></p>
@endsection
