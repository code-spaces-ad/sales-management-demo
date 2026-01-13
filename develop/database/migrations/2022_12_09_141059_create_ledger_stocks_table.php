<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLedgerStocksTable extends Migration
{
    public $table = 'ledger_stocks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id')
                ->comment('ID');
            $table->unsignedInteger('product_id')
                ->nullable()
                ->comment('商品ID');
            $table->string('closing_ym', 6)
                ->nullable()
                ->comment('締年月');
            $table->decimal('ledger_stocks', 15, 4)
                ->default(0)
                ->comment('帳票在庫数');

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
        DB::statement("ALTER TABLE $this->table COMMENT '帳簿在庫数'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // テーブル削除
        Schema::dropIfExists($this->table);
    }
}
