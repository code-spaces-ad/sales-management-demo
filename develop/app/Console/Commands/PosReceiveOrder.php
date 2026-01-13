<?php

/**
 * POS販売データ受信用コントローラー
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
 * POS販売データ受信 コマンドクラス
 */
class PosReceiveOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:pos_receive_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs get pos order data';

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
        // 条件作成
        $conditions = [
            'target_date' => Carbon::now()->format('Y/m/d H:i:s'),
        ];

        $request = new Request();
        $request->merge($conditions);

        $service = new PosApiServices();
        $controller = new PosReceiveController($service);
        $isResult = $controller->receiveOrder($request);
    }
}
