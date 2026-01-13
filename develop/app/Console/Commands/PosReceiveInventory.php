<?php

/**
 * POS棚卸データ受信用コントローラー
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
 * POS棚卸データ受信 コマンドクラス
 */
class PosReceiveInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:pos_receive_inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs get pos inventory data';

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
        // 実行日時 前日 の 22:30:00 をセット
        $target_date = Carbon::now()->addDay(-1)->format('Y/m/d');
        $target_date .= ' 23:00:00';

        // 条件作成
        $conditions = [
            'target_date' => Carbon::now()->format('Y/m/d H:i:s'),
        ];

        $request = new Request();
        $request->merge($conditions);

        $service = new PosApiServices();
        $controller = new PosReceiveController($service);
        $isResult = $controller->receiveInventory($request);
    }
}
