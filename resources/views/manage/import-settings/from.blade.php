@extends('layouts.autowrestle')

@section('title', 'Import from ' . $source->TournamentName . ' – ' . $tournament->TournamentName)

@section('content')
<div class="container main">
    <x-card :padding="true" class="max-w-2xl">
        <h1 class="text-xl font-bold text-slate-900 mb-2">Import Settings</h1>
        <p class="mb-4 text-slate-600">Copy configuration from <strong>{{ $source->TournamentName }}</strong> ({{ $source->TournamentDate ? $source->TournamentDate->format('M j, Y') : '' }}) into <strong>{{ $tournament->TournamentName }}</strong>. Select what to copy. Existing settings of each type will be replaced.</p>
        <p class="mb-6">
            <a href="{{ route('manage.import-settings.index', $tournament->id) }}" class="text-aw-accent hover:underline text-sm">← Choose a different tournament</a>
        </p>

        @if(session('error'))
            <p class="mb-4 rounded-md bg-red-50 px-3 py-2 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        <form method="post" action="{{ route('manage.import-settings.store', $tournament->id) }}" id="import-settings-form">
            @csrf
            <input type="hidden" name="source_tid" value="{{ $source->id }}">

            <div class="space-y-4 mb-6">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="copy_divisions" value="1" class="mt-1 rounded border-slate-300 text-aw-accent focus:ring-aw-accent">
                    <span class="text-slate-900"><strong>Copy Divisions &amp; Groups</strong> — All divisions, their groups, and period timing for each division.</span>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="copy_mats" value="1" id="copy_mats" class="mt-1 rounded border-slate-300 text-aw-accent focus:ring-aw-accent">
                    <span class="text-slate-900"><strong>Copy Mats</strong> — All mats from the source tournament.</span>
                </label>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="copy_bout_numbering" value="1" id="copy_bout_numbering" class="mt-1 rounded border-slate-300 text-aw-accent focus:ring-aw-accent"
                        @if(!$targetHasMats && !$sourceHasMats) disabled @endif>
                    <span class="text-slate-900 @if(!$targetHasMats && !$sourceHasMats) text-slate-500 @endif">
                        <strong>Copy Bout Numbering</strong> — All number schemes. Requires mats in this tournament (select Copy Mats or add mats first).
                    </span>
                </label>
            </div>

            @if(!$targetHasMats && !$sourceHasMats)
                <p class="mb-4 text-amber-700 text-sm">Bout Numbering is disabled because neither this tournament nor the source has mats. Add mats or select Copy Mats to enable it.</p>
            @endif

            <div class="flex gap-3">
                <x-button type="submit" variant="primary">Import</x-button>
                <a href="{{ route('manage.import-settings.index', $tournament->id) }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</a>
            </div>
        </form>
    </x-card>
</div>
<script>
(function () {
    var copyMats = document.getElementById('copy_mats');
    var copyBoutNumbering = document.getElementById('copy_bout_numbering');
    if (!copyMats || !copyBoutNumbering) return;
    var targetHasMats = {{ $targetHasMats ? 'true' : 'false' }};
    function updateBoutNumberingState() {
        var matsSelected = copyMats.checked;
        if (targetHasMats || matsSelected) {
            copyBoutNumbering.disabled = false;
            copyBoutNumbering.closest('label').querySelector('span').classList.remove('text-slate-500');
        } else {
            copyBoutNumbering.checked = false;
            copyBoutNumbering.disabled = true;
            copyBoutNumbering.closest('label').querySelector('span').classList.add('text-slate-500');
        }
    }
    copyMats.addEventListener('change', updateBoutNumberingState);
})();
</script>
@endsection
