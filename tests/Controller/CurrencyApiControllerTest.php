<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CurrencyApiControllerTest extends WebTestCase
{
    public function testCurrencyListEndpointReturnsData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/currency');

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);

        // Проверка наличия некоторых валют
        $this->assertArrayHasKey('USD', $data);
        $this->assertIsNumeric($data['USD']);
    }

    public function testCurrencyConvertSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/convert', [
            'from' => 'USD',
            'to' => 'GBP',
            'amount' => 100
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('result', $data);
        $this->assertIsNumeric($data['result']);
    }

    public function testCurrencyConvertMissingParams(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/convert', [
            'from' => 'USD',
            'amount' => 100
        ]);

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testCurrencyConvertWithInvalidCurrency(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/convert', [
            'from' => 'XXX',
            'to' => 'USD',
            'amount' => 100
        ]);

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testConvertFromEUR(): void
{
    $client = static::createClient();
    $client->request('GET', '/api/convert', [
        'from' => 'EUR',
        'to' => 'USD',
        'amount' => 50
    ]);

    $this->assertResponseIsSuccessful();
    $data = json_decode($client->getResponse()->getContent(), true);
    $this->assertEquals('EUR', $data['from']);
    $this->assertEquals('USD', $data['to']);
    $this->assertGreaterThan(0, $data['result']);
}

public function testConvertToEUR(): void
{
    $client = static::createClient();
    $client->request('GET', '/api/convert', [
        'from' => 'USD',
        'to' => 'EUR',
        'amount' => 20
    ]);

    $this->assertResponseIsSuccessful();
    $data = json_decode($client->getResponse()->getContent(), true);
    $this->assertEquals('USD', $data['from']);
    $this->assertEquals('EUR', $data['to']);
    $this->assertGreaterThan(0, $data['result']);
}

public function testConvertWithSpecificDate(): void
{
    $client = static::createClient();
    $client->request('GET', '/api/convert', [
        'from' => 'USD',
        'to' => 'GBP',
        'amount' => 100,
        'date' => '2025-05-09'
    ]);

    $this->assertResponseIsSuccessful();
    $data = json_decode($client->getResponse()->getContent(), true);
    $this->assertEquals('2024-05-09', $data['date'] ?? '2024-05-09');
    $this->assertGreaterThan(0, $data['result']);
}

public function testConvertWithMissingDateFile(): void
{
    $client = static::createClient();
    $client->request('GET', '/api/convert', [
        'from' => 'USD',
        'to' => 'GBP',
        'amount' => 100,
        'date' => '1900-01-01'
    ]);

    $this->assertResponseStatusCodeSame(404);
    $data = json_decode($client->getResponse()->getContent(), true);
    $this->assertStringContainsString('not found', $data['error']);
}



}
