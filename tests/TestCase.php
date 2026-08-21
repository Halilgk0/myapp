<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Indicates whether the database has been initialized.
     *
     * @var bool
     */
    protected static $databaseInitialized = false;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! static::$databaseInitialized) {
            // Run migrations once for all tests
            $this->artisan('migrate:fresh');
            $this->artisan('db:seed');
            
            static::$databaseInitialized = true;
        }
        
        // Begin a database transaction
        DB::beginTransaction();
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Roll back the transaction
        DB::rollBack();
        
        parent::tearDown();
    }
}
