<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

/**
 * Class CreatesApplication
 * @package Tests
 */
trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
