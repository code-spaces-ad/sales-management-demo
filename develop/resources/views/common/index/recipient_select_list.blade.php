{{-- 納品先リストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('recipient_select_list')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-md-2 col-form-label pl-0 pb-md-3">
            <b>納品先</b>
        </label>
        <div class="flex-md-column col-md-10 pr-md-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="recipient_id"
                        class="custom-select input-recipient-select mr-md-1 select2_search d-none clear-select
                            @if ($errors->has('recipient_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($search_items['recipients'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('recipient_id', $search_condition_input_data['recipient_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-name="{{ $item['name'] }}"
                            data-name-kana="{{ $item['name_kana'] }}"
                            data-customer-id="{{ $item['customer_id'] }}"
                            data-branch-id="{{ $item['branch_id'] }}">
                            {{ $item['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('customer_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
