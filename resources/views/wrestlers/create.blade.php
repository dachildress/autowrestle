@extends('layouts.autowrestle')

@section('title', 'Add Wrestler')

@section('content')
<x-card :padding="true" class="max-w-xl">
    <h1 class="text-xl font-bold text-slate-900 mb-2">Add Wrestler</h1>
    <p class="mb-6"><a href="{{ route('wrestlers.index') }}" class="text-aw-accent hover:underline">← Back to My Wrestlers</a></p>

    @if(session('success'))
        <p class="mb-4 text-green-600 text-sm">{{ session('success') }}</p>
    @endif
    @if($errors->any())
        <ul class="mb-4 text-red-600 text-sm list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    @endif

    <form method="post" action="{{ route('wrestlers.store') }}" id="wrestler-form" class="space-y-4">
        @csrf
        <div>
            <label for="wr_first_name" class="block text-sm font-medium text-slate-700">First Name <span class="text-red-600">*</span></label>
            <input type="text" name="wr_first_name" id="wr_first_name" value="{{ old('wr_first_name') }}" maxlength="30" required placeholder="First Name" class="mt-1 block w-full rounded-md border border-slate-300 text-sm">
        </div>
        <div>
            <label for="wr_last_name" class="block text-sm font-medium text-slate-700">Last Name <span class="text-red-600">*</span></label>
            <input type="text" name="wr_last_name" id="wr_last_name" value="{{ old('wr_last_name') }}" maxlength="30" required placeholder="Last Name" class="mt-1 block w-full rounded-md border border-slate-300 text-sm">
        </div>
        <div>
            <span class="block text-sm font-medium text-slate-700 mb-2">Gender</span>
            <div class="flex gap-4">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="wr_gender" value="Boy" {{ old('wr_gender', 'Boy') === 'Boy' ? 'checked' : '' }} class="rounded-full border border-slate-300 text-aw-primary">
                    <span class="text-sm text-slate-700">Boy</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="wr_gender" value="Girl" {{ old('wr_gender') === 'Girl' ? 'checked' : '' }} class="rounded-full border border-slate-300 text-aw-primary">
                    <span class="text-sm text-slate-700">Girl</span>
                </label>
            </div>
        </div>
        <div>
            <label for="wr_club_select" class="block text-sm font-medium text-slate-700">Club / Team <span class="text-red-600">*</span></label>
            <div class="mt-1 flex gap-2 items-center">
                <select id="wr_club_select" class="block flex-1 rounded-md border border-slate-300 text-sm">
                    <option value="">- Select Club -</option>
                    @foreach($clubs ?? [] as $club)
                        <option value="{{ e($club->Club) }}" {{ old('wr_club') === $club->Club ? 'selected' : '' }}>{{ e($club->Club) }}</option>
                    @endforeach
                    <option value="__other__" {{ old('wr_club_other') ? 'selected' : '' }}>— Other —</option>
                </select>
                <a href="{{ route('wrestlers.clubs.create') }}" class="text-sm text-aw-accent hover:underline whitespace-nowrap">+ Add Club</a>
            </div>
            <input type="text" name="wr_club_other" id="wr_club_other" value="{{ old('wr_club_other') }}" maxlength="30" placeholder="Type club name" class="mt-2 block w-full rounded-md border border-slate-300 text-sm {{ old('wr_club_other') ? '' : 'hidden' }}">
            <input type="hidden" name="wr_club" id="wr_club" value="{{ old('wr_club') }}">
        </div>
        <div>
            <label for="wr_dob" class="block text-sm font-medium text-slate-700">Birth Date</label>
            <div class="mt-1 flex items-center gap-3">
                <input type="text" name="wr_dob" id="wr_dob" value="{{ old('wr_dob') }}" placeholder="MM/DD/YYYY" class="block w-40 rounded-md border border-slate-300 text-sm">
                <span id="age-display" class="text-sm text-slate-600">Age: –</span>
            </div>
            <input type="hidden" name="wr_age" id="wr_age" value="{{ old('wr_age', '') }}">
        </div>
        <div>
            <label for="wr_grade" class="block text-sm font-medium text-slate-700">Grade <span class="text-red-600">*</span></label>
            <select name="wr_grade" id="wr_grade" required class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 text-sm">
                <option value="">- Select Grade -</option>
                @foreach(['PK','K','1','2','3','4','5','6','7','8','9','10','11','12'] as $g)
                    <option value="{{ $g }}" {{ old('wr_grade') === $g ? 'selected' : '' }}>{{ $g }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="wr_weight" class="block text-sm font-medium text-slate-700">Weight (lbs)</label>
            <input type="number" name="wr_weight" id="wr_weight" value="{{ old('wr_weight') }}" min="0" max="500" step="0.1" class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 text-sm">
        </div>
        <div>
            <label for="wr_years" class="block text-sm font-medium text-slate-700">Years of Experience <span class="text-red-600">*</span></label>
            <select name="wr_years" id="wr_years" required class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 text-sm">
                @for($y = 0; $y <= 30; $y++)
                    <option value="{{ $y }}" {{ (string)old('wr_years', '0') === (string)$y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="usawnumber" class="block text-sm font-medium text-slate-700">USA Wrestler Number</label>
            <input type="text" name="usawnumber" id="usawnumber" value="{{ old('usawnumber') }}" maxlength="50" placeholder="" class="mt-1 block w-full max-w-xs rounded-md border border-slate-300 text-sm">
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="inline-flex items-center rounded-md bg-aw-primary px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save Wrestler</button>
            <a href="{{ route('wrestlers.index') }}" class="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</x-card>

<script>
(function() {
    var dob = document.getElementById('wr_dob');
    var ageDisplay = document.getElementById('age-display');
    var ageInput = document.getElementById('wr_age');
    var clubSelect = document.getElementById('wr_club');
    var clubOther = document.getElementById('wr_club_other');

    function parseDate(s) {
        var m = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
        if (!m) return null;
        var month = parseInt(m[1], 10), day = parseInt(m[2], 10), year = parseInt(m[3], 10);
        var d = new Date(year, month - 1, day);
        return (d.getFullYear() === year && d.getMonth() === month - 1 && d.getDate() === day) ? d : null;
    }
    function updateAge() {
        var val = (dob && dob.value) ? dob.value.trim() : '';
        if (!val) { ageDisplay.textContent = 'Age: –'; ageInput.value = ''; return; }
        var d = parseDate(val);
        if (!d) { ageDisplay.textContent = 'Age: –'; ageInput.value = ''; return; }
        var now = new Date();
        var age = now.getFullYear() - d.getFullYear();
        if (now.getMonth() < d.getMonth() || (now.getMonth() === d.getMonth() && now.getDate() < d.getDate())) age--;
        ageDisplay.textContent = 'Age: ' + age + ' years';
        ageInput.value = age;
    }
    if (dob) dob.addEventListener('input', updateAge);

    var clubHidden = document.getElementById('wr_club');
    var form = document.getElementById('wrestler-form');
    if (clubSelect && clubOther && clubHidden) {
        function syncClub() {
            if (clubSelect.value === '__other__') {
                clubOther.classList.remove('hidden');
                clubHidden.value = clubOther.value.trim() || '';
            } else {
                clubOther.classList.add('hidden');
                clubHidden.value = clubSelect.value || '';
            }
        }
        clubSelect.addEventListener('change', function() {
            if (this.value === '__other__') {
                clubOther.classList.remove('hidden');
            } else {
                clubOther.classList.add('hidden');
                clubOther.value = '';
            }
            syncClub();
        });
        clubOther.addEventListener('input', function() { clubHidden.value = this.value.trim(); });
        if (form) form.addEventListener('submit', function() { syncClub(); });
        syncClub();
    }
})();
</script>
@endsection
