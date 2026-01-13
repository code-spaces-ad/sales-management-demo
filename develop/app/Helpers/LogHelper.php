<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * ログヘルパークラス
 */
class LogHelper
{
    /**
     * Log Report
     *
     * @param $e
     * @param string $msg
     */
    public static function report($e, string $msg = '')
    {
        if ($msg) {
            Log::error($msg);
        }
        report($e);
    }

    /**
     * Error Log
     *
     * @param string $class_name
     * @param string $message1
     * @param string $message2
     */
    public static function error(string $class_name, string $message1, string $message2)
    {
        $message = "[$class_name]" . PHP_EOL . "$message1 / $message2";
        Log::error($message);
    }

    /**
     * Error Log
     *
     * @param string $class_name
     * @param string $message1
     * @param string $message2
     */
    public static function info(string $class_name, string $message1, string $message2)
    {
        $message = "[$class_name]" . PHP_EOL . "$message1 / $message2";
        Log::info($message);
    }
}
