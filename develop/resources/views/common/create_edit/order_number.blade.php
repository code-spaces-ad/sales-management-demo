{{-- 伝票番号Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('order_number')
    <div class="form-group d-md-inline-flex col-md-6 my-1 d-flex align-items-center">
        <label class="col-form-label col-md-2 my-1 pl-0">
            <b>{{ $title }}</b>
        </label>
        <div class="col-md-5 pl-0">
            <input type="text" class="form-control" id="order_number" disabled value="{{ $order_number }}">
        </div>
    </div>
@show
