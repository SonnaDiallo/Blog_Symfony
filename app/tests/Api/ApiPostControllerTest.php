<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiPostControllerTest extends WebTestCase
{
    public function testApiPostsListReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/posts');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
    }

    public function testApiPostsListWithPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/posts?page=1&limit=5');

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(1, $data['meta']['page']);
        $this->assertEquals(5, $data['meta']['limit']);
    }

    public function testApiPostsLatest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/posts/latest/3');

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertLessThanOrEqual(3, count($data['data']));
    }

    public function testApiPostNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/posts/99999');

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertFalse($data['success']);
    }
}
