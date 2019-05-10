<?php

namespace AppTest;

use Laravel\Lumen\Testing\TestCase as LaravelTestCase;

abstract class TestCase extends LaravelTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
