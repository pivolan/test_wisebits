<?php


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    public function testVisit()
    {
        $client = static::createClient();
        $client->request("POST", "/api/v1/track", [], [], [], '{"country":"FR"}');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('{"status":"ok","count":', $client->getResponse()->getContent(),);
    }

    public function testVisit400()
    {
        $client = static::createClient();
        $client->request("POST", "/api/v1/track", [], [], [], '{"country":"F"}');
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('Validation Failed', $client->getResponse()->getContent());
    }

    public function testStat200()
    {
        $client = static::createClient();
        $client->request("GET", "/api/v1/track/stat");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('{"status":"ok","result":', $client->getResponse()->getContent(),);
    }
}