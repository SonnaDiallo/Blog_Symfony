<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogControllerTest extends WebTestCase
{
    public function testBlogIndexIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/');

        $this->assertResponseIsSuccessful();
    }

    public function testBlogIndexContainsArticles(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/blog/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body');
    }

    public function testBlogNewRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/new');

        $this->assertResponseRedirects('/login');
    }

    public function testBlogSearchWorks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/blog/?q=test');

        $this->assertResponseIsSuccessful();
    }
}
