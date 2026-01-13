<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterAccountingCodesConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table = 'sales_gift_coupon';
        Schema::create($table, function (Blueprint $table) {
            $table->unsignedInteger('sales_order_id')
                ->comment('売上伝票ID');
            $table->string('name', MasterAccountingCodesConst::NAME_MAX_LENGTH)
                ->nullable()
                ->comment('名前');
            $table->decimal('value', 15 ,4)
                ->default(0)
                ->comment('金額');
            $table->string('note', MasterAccountingCodesConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
            $table->unsignedInteger('sort')
                ->comment('ソート');
            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');
            $table->dateTime('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日時');
            $table->dateTime('deleted_at')
                ->nullable()
                ->comment('削除日時');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '売上商品券'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_gift_coupon');
    }
};
