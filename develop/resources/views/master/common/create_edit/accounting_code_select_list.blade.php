{{-- 経理コードリストBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('accounting_code_select_list')
    <div class="form-group row my-3">
        <label class="col-sm-2 col-form-label">
            <b>経理コード</b>
            @if(isset($required_accounting_code))
                <span class="badge badge-danger">必須</span>
            @endif
        </label>
        <div class="flex-md-column col-sm-3">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <select name="accounting_code_id" onchange=""
                        id="mySelect2"
                        class="custom-select input-accounting-code-select col-9 px-0 mr-md-1 select2_search d-none
                        @if($errors->has('accounting_code_id')) is-invalid @endif">
                    <option value="">-----</option>
                    @foreach (($input_items['accounting_code'] ?? []) as $item)
                        <option
                            @if ($item['id'] == old('accounting_code_id', $target_record_data['accounting_code_id'] ?? null))
                                selected
                            @endif
                            value="{{ $item['id'] }}"
                            data-name="{{ $item['name'] }}"
                            data-code="{{ $item['code'] }}"
                            data-name-kana="{{ $item['name_kana'] }}">
                            {{ StringHelper::getNameWithId($item['code_zerofill'], $item['name']) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('accounting_code_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
