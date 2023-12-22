<?php

namespace Delaney\Superban\Tests\Unit;

include_once __DIR__.'/../helpers/helpers.php';

use DateInterval;
use DateTime;
use Delaney\Superban\Middleware\Superban;
use Delaney\Superban\SuperbanServiceProvider;
use Delaney\Superban\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SuperbanTest extends TestCase
{
    protected $uri;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Define routes setup.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function defineRoutes($router)
    {
        $this->uri = 'api/' . generateRandomString();
        $router->get($this->uri, function () {
            })
            ->middleware('superban:20,2,1440');
    }

    /**
     * Test that the middleware bans a user correctly, and unbans after expiry
     *
     * @test
     */
    public function it_bans_a_user_successfully()
    {
        $maxAttempts = 20;
        $now = new DateTime();
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];

        $response = $this->json(
            'GET',
            $this->uri,
            [],
            $headers
        );

        $response->assertStatus(200);

        // Exhaust attempts
        for ($x = $maxAttempts; $x > 0; $x--) {
            $response = $this->json(
                'GET',
                $this->uri,
                [],
                $headers
            );
        }

        $response = $this->json(
            'GET',
            $this->uri,
            [],
            $headers
        );
        $response->assertStatus(403);
        $this->assertStringContainsString("You have been banned temporarily.", $response->__toString());

        // User is banned for 1 day; Jump into the future

        $now = $now->add(new DateInterval('P1D'));
        $this->travelTo($now);

        $response = $this->json(
            'GET',
            $this->uri,
            [],
            $headers
        );

        $response->assertStatus(200);
    }

    /**
     * Test that keys on the User model are correctly scanned
     *
     * @test
     */
    public function it_bans_a_user_successfully()
    {
        $maxAttempts = 20;
        $now = new DateTime();
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];

        $response = $this->json(
            'GET',
            $this->uri,
            [],
            $headers
        );

        $response->assertStatus(200);

        // Exhaust attempts
        for ($x = $maxAttempts; $x > 0; $x--) {
            $response = $this->json(
                'GET',
                $this->uri,
                [],
                $headers
            );
        }

        $response = $this->json(
            'GET',
            $this->uri,
            [],
            $headers
        );
        $response->assertStatus(403);
        $this->assertStringContainsString("You have been banned temporarily.", $response->__toString());

        // User is banned for 1 day; Jump into the future

        $now = $now->add(new DateInterval('P1D'));
        $this->travelTo($now);

        $response = $this->json(
            'GET',
            $this->uri,
            [],
            $headers
        );

        $response->assertStatus(200);
    }
}
