<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\UserRoleType;
use Illuminate\Support\Facades\Auth;

/**
 * ユーザー用ヘルパークラス
 */
class UserHelper
{
    /**
     * 権限が従業員かチェック
     *
     * @param $role_id
     * @return bool
     */
    public static function isRoleEmployee($role_id = null): bool
    {
        if (is_null($role_id)) {
            $role_id = Auth::user()->role_id;
        }

        if ($role_id == UserRoleType::EMPLOYEE) {
            return true;
        }

        return false;
    }
}
