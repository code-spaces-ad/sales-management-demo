{{-- 更新者/更新日時テーブルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('updated_table')
    <div class="form-group d-md-inline-flex col-md-6 my-0">
        <div class="table-responsive table-fixed-sm
                 @if (!$is_edit_route) invisible @endif"
             style="max-height: 100px;">
            <table class="table table-bordered table-responsive-org table-sm m-0">
                <thead class="thead-light text-center">
                <tr>
                    <th scope="col" class="col-md-6">更新者</th>
                    <th scope="col" class="col-md-6">更新日時</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-center align-middle" data-title="更新者">
                        {{ $target_record_data->updated_name }}
                    </td>
                    <td class="text-center align-middle" data-title="更新日時">
                        {{ $target_record_data->updated_at_slash }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
@show
