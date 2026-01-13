<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterBranchesConst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 支所マスターテーブル（m_branches）作成
 */
class CreateMBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'm_branches';
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->string('name')
                ->comment('支所名');
            $table->string('mnemonic_name', MasterBranchesConst::MNEMONIC_NAME_MAX_LENGTH)
                ->comment('支所名略称');
            $table->unsignedinteger('customer_id')
                ->comment('得意先ID');

            $table->dateTime('created_at')
                ->useCurrent()
                ->comment('作成日時');
            $table->dateTime('updated_at')
                ->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))
                ->comment('更新日時');
            $table->dateTime('deleted_at')
                ->nullable()
                ->comment('削除日時');

            // 外部キー設定
            $table->foreign('customer_id')
                ->references('id')
                ->on('m_customers');
        });

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '支所マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_branches');
    }
}
