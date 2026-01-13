<?php

/**
 * POS仕入データ受信用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Console\Commands;

use App\Http\Controllers\Api\PosReceiveController;
use App\Services\Api\PosApiServices;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

/**
 * POS仕入データ受信 コマンドクラス
 */
class PosReceivePurchase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:pos_receive_purchase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs get pos purchase data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // 直近の更新日をセット
        //        $data1 = InventoryData::query()
        //            ->orderBy('updated_at', 'desc')
        //            ->first('updated_at');
        //        $target_date = $data1['updated_at'];

        // 実行日時 前日 の 22:00:00 をセット
        $target_date = Carbon::now()->addDay(-1)->format('Y/m/d');
        $target_date .= ' 22:00:00';

        // 条件作成
        $conditions = [
            'target_date' => $target_date,
        ];

        $request = new Request();
        $request->merge($conditions);

        $service = new PosApiServices();
        $controller = new PosReceiveController($service);
        $isResult = $controller->receivePurchase($request);
    }
}
