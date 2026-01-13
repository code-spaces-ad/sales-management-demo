{{-- 検索・クリアボタンBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('search_clear_button')
    <div class="col-md-12 px-0">
        <div class="text-center">
            <button type="button" class="btn btn-secondary mr-2" onclick="clearInput();">
                <i class="fas fa-times"></i>
                クリア
            </button>
            {{-- 検索ボタン --}}
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> 検索
            </button>

        </div>
    </div>
@show
