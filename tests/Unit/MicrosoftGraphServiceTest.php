<?php

namespace Tests\Unit;

use App\Services\MicrosoftGraphService;
use Tests\TestCase;

class MicrosoftGraphServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    protected $service;
    protected $accessToken;
    public function testGetAccessToken()
    {
        $this->service = new MicrosoftGraphService();
        $this->accessToken = $this->service->getAccessToken();

        $this->assertNotEmpty($this->accessToken);
    }

    public function testCall()
    {
        $this->service = new MicrosoftGraphService();
        $this->accessToken = $this->service->getAccessToken();

        $this->service->setAccessToken($this->accessToken);

        $this->assertIsArray($this->service->getUsers()['value']);
    }
}
