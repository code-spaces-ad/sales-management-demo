<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use App\Consts\DB\Master\MasterDepartmentsConst;
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
        $table = 'm_departments';
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedInteger('code')
                ->comment('コード値');
            $table->string('name', MasterDepartmentsConst::NAME_MAX_LENGTH)
                ->comment('名前');
            $table->string('name_kana', MasterDepartmentsConst::NAME_KANA_MAX_LENGTH)
                ->nullable()
                ->comment('名前ｶﾅ');
            $table->string('mnemonic_name', MasterDepartmentsConst::NAME_MAX_LENGTH)
                ->nullable()
                ->comment('略称');
            $table->unsignedInteger('manager_id')
                ->nullable()
                ->comment('責任者');
            $table->string('note', MasterDepartmentsConst::NOTE_MAX_LENGTH)
                ->nullable()
                ->comment('備考');
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
        DB::statement("ALTER TABLE $table COMMENT '部門マスター'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_departments');
    }
};
