<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Seed basic data needed for tests
     */
    protected function seedBasicData(): void
    {
        // Create a default department for testing
        \App\Models\Department::create([
            'name' => 'Computer Science',
            'code' => 'CS',
            'description' => 'Computer Science Department for testing'
        ]);
    }
}
