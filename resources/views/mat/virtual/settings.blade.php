@extends('layouts.mat')

@section('title', 'Virtual display – Settings')
@section('panel_title', 'Settings')

@section('content')
@include('mat.nav')

@if($matNumber === null)
    <p class="error">You have no mat assigned. Assign a mat to use the virtual audience display.</p>
@else
    <p>The virtual display always shows the <strong>current match</strong> (the bout you have open for scoring, or the next bout on your mat). Points and timer update live. Click <strong>Display</strong> to open the scoreboard in a new window.</p>

    <div class="form-horizontal" style="max-width: 420px; margin-top: 1rem;">
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="virtual-layout"><strong>Layout:</strong></label>
            <select name="layout" id="virtual-layout" style="display: block; width: 100%; padding: 0.5rem; margin-top: 0.25rem;">
                <option value="">select a layout</option>
                @foreach($layouts as $value => $label)
                    <option value="{{ $value }}" {{ $value === 'Folkstyle' ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="virtual-font"><strong>Font Size:</strong></label>
            <input type="number" name="font" id="virtual-font" value="84" min="24" max="200" style="width: 6em; padding: 0.35rem;"> <span>px</span>
        </div>
        <div class="form-group" style="margin-top: 1.5rem;">
            <button type="button" class="btn btn-primary" id="virtual-display-btn">Display</button>
        </div>
    </div>

    <script>
    document.getElementById('virtual-display-btn').onclick = function() {
        var layout = document.getElementById('virtual-layout').value || 'Folkstyle';
        var font = document.getElementById('virtual-font').value || '84';
        var url = '{{ route("mat.virtual.display") }}?layout=' + encodeURIComponent(layout) + '&font=' + encodeURIComponent(font);
        window.open(url, 'virtual', 'width=640,height=480,menubar=no,toolbar=no,location=no,status=no,scrollbars=yes');
    };
    </script>
@endif
@endsection
