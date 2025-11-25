<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_returns_successful_response()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_api_base_route_works()
    {
        $response = $this->getJson('/api');
        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Academic Nexus Portal API'
                ]);
    }
}
