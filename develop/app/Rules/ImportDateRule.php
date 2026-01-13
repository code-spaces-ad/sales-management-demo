<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Rules;

use DateTime;
use Illuminate\Contracts\Validation\Rule;

/**
 * 取り込み用 日付バリデーションルール
 */
class ImportDateRule implements Rule
{
    protected $dateFormats = [];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $dateFormats = [])
    {
        if (empty($dateFormats)) {
            $dateFormats = ['Y/m/d', 'Y/n/d', 'Y/m/j', 'Y/n/j', 'Y-m-d', 'Y-n-d', 'Y-m-j', 'Y-n-j'];
        }
        $this->dateFormats = $dateFormats;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // 数値かどうか判定
        $result = is_numeric($value);
        if ($result) {
            // 範囲内か判定
            if ($value >= 1 && $value <= 2958465) {
                return true;
            }

            return false;
        }

        foreach ($this->dateFormats as $key => $format) {
            $date = DateTime::createFromFormat('!' . $format, $value);
            $isValidated = $date && $date->format($format) == $value;

            if ($isValidated) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.custom.import_date');
    }
}
