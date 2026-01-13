<?php

/**
 * ベース用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * ベース用コントローラー
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
}
