{{-- メモBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('memo')
    <div class="form-group d-md-inline-flex col-md-6 my-1">
        <label class="col-form-label col-md-3 my-1 pl-0">
            <b>メモ</b>
        </label>
        <div class="flex-md-column col-md-9 pr-md-0 pl-0">
            <div class="d-md-inline-flex col-md-12 pr-md-0 pl-0">
                <textarea name="memo" class="form-control input-note{{ $errors->has('memo') ? ' is-invalid' : '' }}" id="memo" rows="2" placeholder="帳票には出力されません。ご自由にお使いください。">{{ old('memo', $target_record_data['memo'] ?? null) }}</textarea>
            </div>
            @error('memo')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
@show
