<?php

/**
 * トークンリフレッシュ ミドルウェア
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * トークンリフレッシュ ミドルウェア
 */
class RegenerateToken
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->method() === 'POST' || $request->method() === 'PUT') {
            // POSTかPUTのときにトークンリフレッシュ(formの多重送信対策)
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
