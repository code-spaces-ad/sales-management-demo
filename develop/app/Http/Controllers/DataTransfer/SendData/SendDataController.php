<?php

/**
 * POSデータ送信用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\DataTransfer\SendData;

use App\Enums\PosSendApiType;
use App\Helpers\PosApiHelper;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * POSデータ送信用コントローラー
 */
class SendDataController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * POSデータ送信画面
     *
     * @return View
     */
    public function index(): View
    {
        $data = [
            /** 検索項目 */
            'search_items' => [
                'pos_send_api_id' => PosSendApiType::asSelectArray(),
                'pos_send_api_url' => PosApiHelper::getPosSendApiUrl(),
            ],
            'default_datetime_local' => now()->format('Y-m-d\TH:i'),
        ];

        return view('data_transfer.send_data.index', $data);
    }
}
