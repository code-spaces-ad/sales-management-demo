<?php

/**
 * 初期値関連定数
 *
 * @copyright © 2025 CodeSpaces
 */

return [

    /** ログ保持日数 */
    'log_retention_days' => 30,

    /** ログ対象ルート名 */
    'accept_route_name' => [
        'login' => true,
        'logout' => false,
        'index' => false,
        'create' => false,
        'edit' => false,
        'store' => true,
        'update' => true,
        'destroy' => true,
        'import' => true,
        /** その他(上記以外) */
        'other' => false,
    ],
];
