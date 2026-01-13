{{-- 伝票日付Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('transaction_type_checkbox')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-2 pl-0 pb-md-3">
            <b>伝票種別</b>
        </label>
        <div class="col-md-10 pr-md-0">
            @foreach($search_items['transaction_types'] as $id => $name)
                <div class="form-check form-check-inline mr-4">
                    <input
                        type="checkbox"
                        name="transaction_type[]"
                        value="{{ $id }}"
                        id="transaction-type-item-{{ $id }}"
                        class="form-check-input input-transaction-type{{ $errors->has('transaction_type.*') ? ' is-invalid' : '' }} clear-check"
                        {{ in_array($id, old('transaction_type', $search_condition_input_data['transaction_type'] ?? [])) ? 'checked' : '' }}>
                    {{ $name }}
                </div>
            @endforeach
            @error('transaction_type.*')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
