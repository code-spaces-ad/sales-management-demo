<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Rules;

use BenSampo\Enum\Rules\EnumValue;

/**
 * EnumValue カスタマイズ用バリデーションルール
 * ※メッセージ部分だけオーバーライド
 */
class EnumValueCustom extends EnumValue
{
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.custom.enum_value');
    }
}
