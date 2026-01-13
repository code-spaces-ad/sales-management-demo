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
        $table = 'm_office_facilities';
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->unsignedInteger('code')
                ->comment('コード値');
            $table->unsignedInteger('department_id')
                ->comment('部門ID');
            $table->string('name', MasterDepartmentsConst::NAME_MAX_LENGTH)
                ->comment('名前');
            $table->unsignedInteger('manager_id')
                ->nullable()
                ->comment('担当者');
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
        DB::statement("ALTER TABLE $table COMMENT '事業所マスター'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_office_facilities');
    }
};
