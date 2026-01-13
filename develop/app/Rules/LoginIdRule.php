<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * ログインID用バリデーションルール
 */
class LoginIdRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        return preg_match('/^[a-zA-Z0-9-._]+$/u', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.custom.login_id');
    }
}
