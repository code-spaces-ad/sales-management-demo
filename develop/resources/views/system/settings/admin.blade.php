@if (auth()->user()->role_id === UserRoleType::SYS_ADMIN)
    {{--  システム管理権限のみ  --}}
    <div class="col-md-12 py-4 setting-item admin" style="background-color:lightpink!important;">
        <div>
            <h1>システム管理者設定</h1>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="d-flex justify-content-center align-items-center">
                            <h2 class="mb-0">通知設定</h2>
                        </div>
                    </div>
                    <div class="card-body approval-card-body">
                        <table class="table table-bordered mb-0">
                            <tbody>
                            <tr>
                                <td class="align-middle" style="width: 80%">メール送信</td>
                                <td class="align-middle" style="width: 20%">
                                    <div class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="notification[send_mail]" value="0">
                                            <input type="checkbox" name="notification[send_mail]" value="1"
                                                   class="custom-control-input" id="send_mail"
                                                {{ !empty($settings['send_mail']) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="send_mail"></label>
                                        </div>
                                        @error('notification.send_mail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="align-middle">エラーメール送信</td>
                                <td class="align-middle">
                                    <div class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="notification[send_error_mail]" value="0">
                                            <input type="checkbox" name="notification[send_error_mail]" value="1"
                                                   class="custom-control-input" id="send_error_mail"
                                                {{ !empty($settings['send_error_mail']) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="send_error_mail"></label>
                                        </div>
                                        @error('notification.send_error_mail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle">エラーTeams送信</td>
                                <td class="align-middle">
                                    <div class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="notification[send_error_teams]" value="0">
                                            <input type="checkbox" name="notification[send_error_teams]" value="1"
                                                   class="custom-control-input" id="send_error_teams"
                                                {{ !empty($settings['send_error_teams']) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="send_error_teams"></label>
                                        </div>
                                        @error('notification.send_error_teams')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">エラーTeams通知用WebhookURL</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">
                                    <div class="text-left">
                                        <div class="custom-control">
                                            <textarea name="notification[error_teams_webhook_url]"
                                                      id="error_teams_webhook_url"
                                                      style="width: 100%;"
                                                      rows="5">{{ isset($settings['error_teams_webhook_url']) ? htmlspecialchars($settings['error_teams_webhook_url'], ENT_QUOTES, 'UTF-8') : '' }}</textarea>
                                        </div>
                                        @error('notification.error_teams_webhook_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="align-middle">ログインTeams送信</td>
                                <td class="align-middle">
                                    <div class="text-center">
                                        <div class="custom-control custom-switch">
                                            <input type="hidden" name="notification[send_login_teams]" value="0">
                                            <input type="checkbox" name="notification[send_login_teams]" value="1"
                                                   class="custom-control-input" id="send_login_teams"
                                                {{ !empty($settings['send_login_teams']) ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                   for="send_login_teams"></label>
                                        </div>
                                        @error('notification.send_login_teams')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">ログインTeams通知用WebhookURL</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="align-middle">
                                    <div class="text-left">
                                        <div class="custom-control">
                                            <textarea name="notification[login_teams_webhook_url]"
                                                      id="login_teams_webhook_url"
                                                      style="width: 100%;"
                                                      rows="5">{{ isset($settings['login_teams_webhook_url']) ? htmlspecialchars($settings['login_teams_webhook_url'], ENT_QUOTES, 'UTF-8') : '' }}</textarea>
                                        </div>
                                        @error('notification.login_teams_webhook_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
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
                        通知について（覚書）
                    </div>
                    <div class="card-body pl-4">
                        <li>develop/config/consts/default.phpの設定を一旦こちらに記述とする</li>
                        <li>システム管理者しか触らない機能をここにおく。</li>
                        <li>ユーザーに流しても良い場合は画面を調整する</li>
                    </div>
                </div>
            </div>
        </div>

        <div class="row my-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <div class="d-flex justify-content-center align-items-center">
                            <h2 class="mb-0">.env設定</h2>
                        </div>
                    </div>
                    <div class="card-body approval-card-body">
                        {!!nl2br(htmlspecialchars($env))!!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
