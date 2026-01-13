@if (auth()->user()->role_id === UserRoleType::SYS_ADMIN)
    {{--  システム管理権限のみ  --}}
    <div class="col-md-12 py-4 setting-item report" style="background-color:lightpink!important;">
        <div>
            <h1>帳票設定(システム管理者)</h1>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="d-flex justify-content-center align-items-center">
                            <h2 class="mb-0">
                                <a href="{{ route('report_output.sale.bank_transfer_fee.index') }}">
                                    {{-- 振込手数料 --}}
                                    <span>{{ config('consts.title.report_output.menu.bank_transfer_fee') }}</span>
                                </a>
                            </h2>
                        </div>
                    </div>
                    <div class="card-body approval-card-body">
                        <table class="table table-bordered mb-0">
                            <tbody>
                            <tr>
                                <td colspan="2" class="align-middle">出力対象事業所</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">
                                    <div class="text-left">
                                        <div class="custom-control">
                                            @php
                                                $previous_department = null;
                                            @endphp
                                            @foreach($input_items['office_facilities'] as $office_facility)
                                                @if ($previous_department !== $office_facility->department_name)
                                                    <h5 class="mt-3">■{{ $office_facility->department_name }}</h5>
                                                    @php
                                                        $previous_department = $office_facility->department_name;
                                                    @endphp
                                                @endif

                                                <div class="icheck-primary icheck-inline mr-2 pl-3 my-0">
                                                    <input type="checkbox"
                                                           name="report[bank_transfer_fee_target_office_facilities][]"
                                                           id="bank-transfer-fee-target-office-facilities-item-{{ $office_facility->id }}"
                                                           value="{{ $office_facility->id }}"
                                                           {{ in_array($office_facility->id, explode(',', $settings['bank_transfer_fee_target_office_facilities']) ?? []) ? 'checked' : '' }}
                                                           class="form-check-input input-bank-transfer-fee-target-office-facilities clear-check{{ $errors->has('bank_transfer_fee_target_office_facilities.*') ? ' is-invalid' : '' }}">

                                                    <label class="form-check-label"
                                                           for="bank-transfer-fee-target-office-facilities-item-{{ $office_facility->id }}">
                                                        {{ $office_facility->code }}:{{ $office_facility->name }}
                                                    </label>
                                                </div>
                                            @endforeach

                                        </div>
                                        @error('report.bank_transfer_fee_target_office_facilities')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">ソート順(事業所コード カンマ区切り) ※任意</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">
                                    <div class="text-left">
                                        <div class="custom-control">
                                            <input type="text" name="report[bank_transfer_fee_sort]" class="col-md-12"
                                                   id="bank_transfer_fee_sort"
                                                   value="{{ $settings['bank_transfer_fee_sort']}}">
                                        </div>
                                        @error('report.bank_transfer_fee_sort')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">事業所名除去文字列(カンマ区切り) ※任意</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">
                                    <div class="text-left">
                                        <div class="custom-control">
                                            <input type="text" name="report[bank_transfer_fee_replace_blank]" class="col-md-12"
                                                id="bank_transfer_fee_replace_blank"
                                                value="{{ $settings['bank_transfer_fee_replace_blank']}}">
                                        </div>
                                        @error('report.bank_transfer_fee_replace_blank')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card">
                    以下テンプレ
                    <div class="card-header d-flex justify-content-between">
                        <div class="d-flex justify-content-center align-items-center">
                            <h2 class="mb-0">※ここに帳票名</h2>
                        </div>
                    </div>
                    <div class="card-body approval-card-body">
                        <table class="table table-bordered mb-0">
                            <tbody>
                            <tr>
                                <td colspan="2" class="align-middle">※ここに設定名</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">
                                    <div class="text-left">
                                        ※ここに設定値
                                        {{--                                        <div class="custom-control">--}}
                                        {{--                                            <textarea name="notification[error_teams_webhook_url]"--}}
                                        {{--                                                      id="error_teams_webhook_url"--}}
                                        {{--                                                      style="width: 100%;"--}}
                                        {{--                                                      rows="5">{{ isset($settings['error_teams_webhook_url']) ? htmlspecialchars($settings['error_teams_webhook_url'], ENT_QUOTES, 'UTF-8') : '' }}</textarea>--}}
                                        {{--                                        </div>--}}
                                        {{--                                        @error('notification.error_teams_webhook_url')--}}
                                        {{--                                        <div class="invalid-feedback">{{ $message }}</div>--}}
                                        {{--                                        @enderror--}}
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header" style="font-size: 1.2rem; font-weight: bold">
                        帳票設定について（覚書）
                    </div>
                    <div class="card-body pl-4">
                        <li>2025/04/22時点 <b style="color: red">システム管理者</b>のみの設定となります。</li>
                        <li>ユーザーに流しても良い場合は画面を調整する</li>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endif
