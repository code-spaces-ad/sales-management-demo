{{-- 伝票日付Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('order_date')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-3 pl-0 pb-md-3">
            <b>伝票日付</b>
            <span class="badge badge-danger">必須</span>
        </label>
        <div class="flex-md-column col-md-4 pl-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <input type="date" name="order_date" id="order_date" value="{{ old('order_date', $target_record_data['order_date'] ?? $default_order_date) }}" 
                    class="form-control input-order-date{{ $errors->has('order_date') ? ' is-invalid' : '' }}">
            </div>
            @error('order_date')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
