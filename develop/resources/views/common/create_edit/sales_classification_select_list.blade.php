{{-- 売上分類リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('sales_classification_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-3 col-form-label pl-0 pb-md-3">
            <b>分類</b>
            @if(isset($required_sales_classification))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-md-9 pr-md-0 pl-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select id="sales_classification"
                        name="sales_classification_id"
                        class="custom-select input-sales-classification-select mr-md-1 select2_search d-none
                    @if($errors->has('sales_classification_id')) is-invalid @endif">
                    @if(!isset($required_sales_classification))
                        <option value="">-----</option>
                    @endif
                    @foreach (($input_items['sales_classifications'] ?? []) as $key => $val)
                        <option
                            @if ($key == old('sales_classification_id', $target_record_data['sales_classification_id'] ?? null))
                                selected
                            @endif
                            value="{{ $key }}"
                            data-name="{{ $val }}"
                            data-sales-classification-id="{{ $key }}">
                            {{ $val }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('sales_classification_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
