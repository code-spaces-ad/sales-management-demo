<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Master;

use App\Consts\DB\Master\MasterCustomersConst;
use App\Consts\DB\Master\MasterUsersConst;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterRole;
use App\Models\Master\MasterUser;
use App\Rules\LoginIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ユーザーマスター作成編集用 リクエストクラス
 */
class UserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $table = with(new MasterUser())->getTable();    // テーブル名
        $user_id = $this->route()->parameter('user')->id ?? null;
        $is_edit_request = $this->routeIs('system.users.edit*') || $this->routeIs('system.users.update');
        $login_id = $this->route()->parameter($table)->login_id ?? null;

        $login_id_validation_rule = [
            'bail',
            'required',
            'string',
            'min:' . MasterUsersConst::LOGIN_ID_MIN_LENGTH,
            'max:' . MasterUsersConst::LOGIN_ID_MAX_LENGTH,
            Rule::unique($table)->ignore($login_id, 'login_id'),
            'email:strict,dns',
        ];

        if (!$this->existAtmarkLoginId($this->login_id)) {
            // アットマークを含まなければ、ログインIDルールでチェック
            $login_id_validation_rule[6] = new LoginIdRule();
        }

        $password_validation_rule = ['bail', 'required', 'string',
            'min:' . MasterUsersConst::PASSWORD_MIN_LENGTH, 'max:' . MasterUsersConst::PASSWORD_MAX_LENGTH, ];

        if ($is_edit_request && $user_id !== null) {
            $login_id_validation_rule[5] = Rule::unique($table, 'login_id')->ignore($user_id);
            $password_validation_rule[1] = 'nullable';
        }

        $email_unique = 'unique:m_users,email';
        if ($is_edit_request) {
            $email_unique = 'unique:m_users,email,' . $this->route()->parameter('user')->id;
        }

        return [
            'code' => ['bail', 'required', 'numeric', 'min:' . MasterUsersConst::CODE_MIN_VALUE,
                'max:' . MasterUsersConst::CODE_MAX_VALUE, Rule::unique($table)->ignore($user_id)->whereNull('deleted_at')],
            'login_id' => $login_id_validation_rule,
            'password' => $password_validation_rule,
            'email' => ['bail', 'required', 'string', 'email', 'max:' . MasterCustomersConst::EMAIL_MAX_LENGTH, $email_unique],
            'name' => ['bail', 'required', 'string'],
            'employee_id' => ['bail', 'nullable', 'integer', 'exists:' . with(new MasterEmployee())->getTable() . ',id'],
            'note' => ['bail', 'nullable', 'string', 'max:' . MasterUsersConst::MEMO_MAX_LENGTH],
            'role_id' => ['bail', 'required', 'integer',
                'exists:' . with(new MasterRole())->getTable() . ',id', ],
        ];
    }

    /**
     * 定義済みバリデーションルールのエラーメッセージ取得
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * 項目名
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'code' => 'コード',
            'login_id' => 'ログインID',
            'password' => 'パスワード',
            'email' => 'メールアドレス',
            'name' => '名前',
            'employee_id' => '担当者',
            'note' => '備考',
            'role_id' => '権限',
        ];
    }

    /**
     * Get the URL to redirect to on a validation error.
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        $user_id = $this->route()->parameter('user')->id ?? null;
        $is_edit_request = $this->routeIs('system.users.edit*') || $this->routeIs('system.users.update');

        if ($is_edit_request && $user_id !== null) {
            return route('system.users.edit', $user_id);
        }

        return route('system.users.create');
    }

    /**
     * ログインIDに「@」が含まれるかをチェック
     *
     * @param string $login_id
     * @return bool|int
     */
    private function existAtmarkLoginId($login_id)
    {
        return strpos($login_id, '@') !== false;
    }
}
