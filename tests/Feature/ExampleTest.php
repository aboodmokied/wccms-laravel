<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that unauthenticated root request redirects per Phase 3 auth specification.
     */
    public function test_unauthenticated_root_request_redirects(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }
}
