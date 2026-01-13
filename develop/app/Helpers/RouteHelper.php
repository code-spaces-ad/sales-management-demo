<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

/**
 * ルート ヘルパークラス
 */
class RouteHelper
{
    /**
     * 遷移前のルートを取得する
     *
     * @return string
     */
    public static function getPreviousRoute(): string
    {
        return app('router')->getRoutes()
            ->match(app('request')->create(url()->previous()))->getName();
    }
}
