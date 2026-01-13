<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Console\Commands;

use App\Helpers\InventoryDataClosingHelper;
use App\Models\Inventory\InventoryStockData;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * 現在庫データ登録 コマンドクラス
 */
class InventoryDataClosing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_or_insert:inventory_data_closing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register current inventory data';

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
        $this->registInventoryDataClosing();
    }

    /**
     * 締在庫数を登録
     *
     * @return void
     */
    public static function registInventoryDataClosing(): void
    {
        $inventory_stocks_data = InventoryStockData::query()
            ->oldest('warehouse_id')
            ->oldest('id')
            ->get();

        foreach ($inventory_stocks_data ?? [] as $detail) {
            InventoryDataClosingHelper::updateOrInsertInventoryDataClosing($detail, Carbon::now()->subMonthNoOverflow()->format('Ym'));
        }
    }
}
