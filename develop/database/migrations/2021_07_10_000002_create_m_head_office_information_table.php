<?php

/**
 * @copyright © 2025 CodeSpaces
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 本社情報マスターテーブル（m_head_office_information）作成
 */
class CreateMHeadOfficeInformationTable extends Migration
{
    /**
    * Run the migrations.
    *
    * @return void
    */
    public function up()
    {
        $table = 'm_head_office_information';
        Schema::create($table, function (Blueprint $table) {
            $table->increments('id')
                ->comment('ID');
            $table->string('company_name', 100)
                ->comment('会社名');
            $table->string('representative_name', 100)
                ->comment('代表者名');
            $table->string('postal_code1', 3)
                ->comment('郵便番号1');
            $table->string('postal_code2', 4)
                ->comment('郵便番号2');
            $table->string('address1', 50)
                ->comment('住所1');
            $table->string('address2', 50)
                ->nullable()
                ->comment('住所2');
            $table->string('tel_number', 13)
                ->comment('電話番号');
            $table->string('fax_number', 13)
                ->nullable()
                ->comment('FAX番号');
            $table->string('email')
                ->nullable()
                ->comment('メールアドレス');
            $table->binary('company_seal_image')
                ->comment('社印画像');
            $table->string('company_seal_image_file_name', 50)
                ->nullable()
                ->comment('社印画像ファイル名');
            $table->string('invoice_number', 14)
                ->nullable()
                ->comment('インボイス登録番号');

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

        // company_seal_image カラム変更 ※MEDIUMBLOB 型に変更するため。
        $sql = "ALTER TABLE $table MODIFY COLUMN company_seal_image MEDIUMBLOB COMMENT '社印画像' ";
        DB::statement($sql);

        // テーブルコメント
        DB::statement("ALTER TABLE $table COMMENT '本社情報マスター'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_head_office_information');
    }
}
