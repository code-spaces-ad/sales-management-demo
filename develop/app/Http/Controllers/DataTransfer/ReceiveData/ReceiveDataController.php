<?php

/**
 * POSデータ受信用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\DataTransfer\ReceiveData;

use App\Enums\PosReceiveApiType;
use App\Helpers\PosApiHelper;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * POSデータ受信用コントローラー
 */
class ReceiveDataController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * POSデータ受信画面
     *
     * @return View
     */
    public function index(): View
    {
        $data = [
            /** 検索項目 */
            'search_items' => [
                'pos_receive_api_id' => PosReceiveApiType::asSelectArray(),
                'pos_receive_api_url' => PosApiHelper::getPosReceiveApiUrl(),
            ],
            'default_datetime_local' => now()->format('Y-m-d\TH:i'),
        ];

        return view('data_transfer.receive_data.index', $data);
    }
}
