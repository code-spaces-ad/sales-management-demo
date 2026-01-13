<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Requests\Define;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

/**
 * 入力・編集画面用 トレイト
 */
trait EditRequestTrait
{
    /**
     * バリデーションエラー時の処理
     *
     * @param Request $request
     * @param Validator $validator
     * @return void
     */
    public static function setTokenAndRedirect(Request $request, Validator $validator): void
    {
        // 生成されたcsrfトークンをセット
        $input = $request->input();
        $input['_token'] = csrf_token();

        // 元の画面へリダイレクト
        $request->redirector
            ->to($request->getRedirectUrl())
            ->withInput($input)
            ->withErrors($validator)
            ->throwResponse();
    }
}
