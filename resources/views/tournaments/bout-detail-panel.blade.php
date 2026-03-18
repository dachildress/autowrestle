{{-- Fragment for bracket page side panel. No layout. --}}
@php
    $redName = trim($red->wr_first_name . ' ' . $red->wr_last_name);
    $greenName = trim($green->wr_first_name . ' ' . $green->wr_last_name);
    $redWins = $state->winner_id === (int) $red->id;
    $greenWins = $state->winner_id === (int) $green->id;
    $redGrade = $red->wr_grade !== null && $red->wr_grade !== '' ? (int) $red->wr_grade : null;
    $greenGrade = $green->wr_grade !== null && $green->wr_grade !== '' ? (int) $green->wr_grade : null;
    $gradeLabel = function ($g) {
        if ($g === null) return '';
        if ($g === -1) return 'P';
        if ($g === 0) return 'K';
        $s = (string) $g;
        if ($s === '1') return '1st';
        if ($s === '2') return '2nd';
        if ($s === '3') return '3rd';
        return $g . 'th';
    };
@endphp
<div class="bout-detail-panel-content flex flex-col h-full">
    <div class="flex items-start justify-between gap-2 border-b border-slate-200 pb-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Bout #{{ $boutNumber }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $weightLabel }}</p>
        </div>
        <button type="button" class="bout-panel-close rounded p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Close">&times;</button>
    </div>

    <div class="grid grid-cols-2 gap-2 my-3">
        <div class="rounded-lg overflow-hidden {{ $redWins ? 'bg-emerald-50 border-l-4 border-emerald-600' : 'bg-white border border-slate-200 border-r-2 border-r-red-400' }}">
            <div class="p-3 relative">
                @if($redWins)<span class="absolute top-2 right-2 font-bold text-emerald-600 text-sm">W</span>@endif
                <p class="font-bold text-slate-900">{{ $redName }}</p>
                @if($redGrade !== null)<p class="text-sm text-slate-600">{{ $gradeLabel($redGrade) }}</p>@endif
                @if($red->wr_club)<p class="text-sm text-slate-500">{{ $red->wr_club }}</p>@endif
            </div>
        </div>
        <div class="rounded-lg overflow-hidden {{ $greenWins ? 'bg-emerald-50 border-l-4 border-emerald-600' : 'bg-white border border-slate-200 border-r-2 border-r-red-400' }}">
            <div class="p-3 relative">
                @if($greenWins)<span class="absolute top-2 right-2 font-bold text-emerald-600 text-sm">W</span>@endif
                <p class="font-bold text-slate-900">{{ $greenName }}</p>
                @if($greenGrade !== null)<p class="text-sm text-slate-600">{{ $gradeLabel($greenGrade) }}</p>@endif
                @if($green->wr_club)<p class="text-sm text-slate-500">{{ $green->wr_club }}</p>@endif
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2 my-3 rounded-lg overflow-hidden border border-slate-200">
        <div class="bg-emerald-600 text-white px-4 py-2 text-center min-w-[4rem]">
            <span class="block text-xs font-medium uppercase">Winner</span>
            <span class="text-xl font-bold">{{ $redWins ? $state->red_score : $state->green_score }}</span>
        </div>
        <div class="flex-1 px-3 py-2 text-center text-sm">
            <p class="font-semibold text-slate-800">{{ $resultLine }}</p>
            @if($matNumber)<p class="text-slate-500 mt-0.5">Mat {{ $matNumber }}</p>@endif
        </div>
        <div class="border-l border-slate-200 px-4 py-2 text-center min-w-[3rem]">
            <span class="text-lg font-bold text-slate-700">{{ $redWins ? $state->green_score : $state->red_score }}</span>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto border-t border-slate-200 pt-3">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Scoring by period</h3>
        @php $periodOrder = $eventsByPeriod->keys()->sort()->values(); @endphp
        @foreach($periodOrder as $periodNum)
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 border-b border-slate-100 pb-1 mb-2">Period {{ $periodNum }}</p>
                <ul class="space-y-2">
                    @foreach($eventsByPeriod->get($periodNum, []) as $e)
                        @php
                            $isRed = $e->side === 'red';
                            $timeStr = $e->match_time_snapshot !== null
                                ? sprintf('%d:%02d', (int)($e->match_time_snapshot / 60), $e->match_time_snapshot % 60)
                                : '';
                        @endphp
                        <li class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-sm {{ $isRed ? 'border-red-300 bg-red-50/50 text-red-800' : 'border-emerald-300 bg-emerald-50/50 text-emerald-800' }}">
                                {{ $e->event_type }}@if($e->points) {{ $e->points }}@endif
                            </span>
                            @if($timeStr)<span class="text-slate-500 text-sm">{{ $timeStr }}</span>@endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
        @if($periodOrder->isEmpty())
            <p class="text-slate-500 text-sm">No scoring events recorded.</p>
        @endif
    </div>
</div>
