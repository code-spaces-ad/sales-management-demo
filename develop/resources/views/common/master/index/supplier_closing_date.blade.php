{{-- 仕入締日Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="form-group d-md-inline-flex col-md-12 my-1">
    <label class="col-md-1 col-form-label pl-0 pb-md-3">
        <b>仕入締日</b>
    </label>
    <div class="col-sm-10">
        @foreach($search_items['closing_date_list'] as $id => $name)
            <div class="icheck-primary icheck-inline mr-2">
                <input type="checkbox" name="closing_date[]" value="{{ $id }}" id="order-status-item-{{ $id }}" 
                    class="form-check-input input-closing-date clear-check{{ $errors->has('closing_date.*') ? ' is-invalid' : '' }}" 
                    {{ in_array($id, $search_condition_input_data['closing_date'] ?? []) ? 'checked' : '' }}>
                <label class="form-check-label" for="order-status-item-{{ $id }}">{{ $name }}日締め</label>
            </div>
        @endforeach
        @error('closing_date')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
