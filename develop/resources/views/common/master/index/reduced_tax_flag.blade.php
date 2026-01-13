{{-- 税率区分Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-12 my-1">
    <label class="col-md-1 col-form-label pl-0 pb-md-3">
        <b>税率区分</b>
    </label>
    <div class="col-sm-10">
        @foreach($search_items['reduced_tax_flag_types'] as $id => $name)
            <div class="icheck-primary icheck-inline mr-2">
                <input type="checkbox" name="reduced_tax_flag[]" value="{{ $id }}" id="reduced-tax-flag-item-{{ $id }}"
                    class="form-check-input input-closing-date clear-check{{ $errors->has('reduced_tax_flag.*') ? ' is-invalid' : '' }}"
                    {{ in_array($id, $search_condition_input_data['reduced_tax_flag'] ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="reduced-tax-flag-item-{{ $id }}">
                    {{ $name }} @if($id === ReducedTaxFlagType::NOT_REDUCED_TAX)(非課税を含む)@endif
                </label>
            </div>
        @endforeach
        @error('reduced_tax_flag')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
