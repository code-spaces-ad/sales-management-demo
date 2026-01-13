{{-- 支所リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('branch_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-3 col-form-label pl-0 pb-md-3">
            <b>支所</b>
        </label>
        <div class="flex-md-column col-md-9 pr-md-0 pl-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="branch_id" onchange="changeBranchCreateEdit();"
                        class="custom-select input-branch-select mr-md-1 select2_search d-none
                        @if($errors->has('branch_id')) is-invalid @endif" disabled>
                    <option value="">-----</option>
                    @foreach (($input_items['branches'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('branch_id', $target_record_data['branch_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-name="{{ $item['name'] }}"
                            data-customer-id="{{ $item['customer_id'] }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('branch_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
