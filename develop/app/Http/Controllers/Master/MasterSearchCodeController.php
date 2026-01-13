<?php

/**
 * マスター画面用コード検索コントローラー
 *
 * @copyright © 2025 レボルシオン株式会社
 */

namespace App\Http\Controllers\Master;

use App\Helpers\CodeHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * マスター画面用コード検索コントローラー
 */
class MasterSearchCodeController extends Controller
{
    /***
     * @param Request $request
     * @return JsonResponse
     */
    public function getNextUsableCode(Request $request): JsonResponse
    {
        $table_name = $request->input('table_name');
        $current_code = $request->input('current_code') ?? 0;
        // 次の空き番を取得
        $next_code = CodeHelper::getNextUsableCode($table_name, $current_code);

        return response()->json(
            [
                'next_code' => $next_code,
            ]
        );
    }

    /***
     * @param Request $request
     * @return JsonResponse
     */
    public function getNextUsableSortCode(Request $request): JsonResponse
    {
        $table_name = $request->input('table_name');
        $current_code = $request->input('current_code') ?? 0;
        // 次の空き番を取得
        $next_sort_code = CodeHelper::getNextUsableSortCode($table_name, $current_code);

        return response()->json(
            [
                'next_sort_code' => $next_sort_code,
            ]
        );
    }
}
