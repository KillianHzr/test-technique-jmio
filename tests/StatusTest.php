<?php


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StatusTest extends WebTestCase
{
    public function testSiteStatus(): void
    {
        $client = static::createClient();

        $client->request('GET', '/status/up');

        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertEquals(200, $statusCode);

        $content = $client->getResponse()->getContent();
        $this->assertJson($content);

        $arrayContent = json_decode($content, true);
        $this->assertArrayHasKey('status', $arrayContent);
        $this->assertEquals('ok', $arrayContent['status']);
    }
}
