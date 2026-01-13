<?php

/**
 * 本社情報マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 本社情報マスターモデル
 */
class HeadOfficeInfo extends Model
{
    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_head_office_information';
}
