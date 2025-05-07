<div class="form-group row">
    <div class="col-sm-1"></div>
    <label class="col-sm-3 col-form-label">{{ $label }}</label>
    <div class="col-sm-4">
        <select class="{{ $class ?? 'form-control select2' }}" name="{{ $name }}" {{ $multiple ? 'multiple' : '' }}>
            {!! $slot !!}
        </select>
    </div>
</div>