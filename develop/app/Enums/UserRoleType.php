<?php

/**
 * ユーザー権限用Enum
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Enums;

use App\Base\BaseEnum;

/**
 * ユーザー権限用Enum
 */
final class UserRoleType extends BaseEnum
{
    /** システム管理者 */
    public const int SYS_ADMIN = 1;

    /** システム運用者 */
    public const int SYS_OPERATOR = 2;

    /** 従業員 */
    public const int EMPLOYEE = 3;

    /**
     * 定数と文字列のマッピング
     *
     * @return array
     */
    public static function mappings(): array
    {
        return [
            self::SYS_ADMIN => 'システム管理者',
            self::SYS_OPERATOR => 'システム運用者',
            self::EMPLOYEE => '従業員',
        ];
    }
}
