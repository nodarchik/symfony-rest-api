<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
    }

    public function testCreate()
    {
        $client = static::createClient();

        $data = [
            'name' => 'John',
            'surname' => 'Doe',
            'personalCode' => '123456',
            'phoneNumber' => '1234567890',
            'dateOfBirth' => '2000-01-01',
            'active' => true,
            'balance' => 1000
        ];

        $client->request('POST', '/api/users', [], [], [], json_encode($data));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        $this->assertEquals('John', $responseData['data']['name']);
        $this->assertEquals('Doe', $responseData['data']['surname']);
        $this->assertEquals('123456', $responseData['data']['personalCode']);
        $this->assertEquals('1234567890', $responseData['data']['phoneNumber']);
        $this->assertEquals('2000-01-01', $responseData['data']['dateOfBirth']);
        $this->assertTrue($responseData['data']['active']);
        $this->assertEquals(1000, $responseData['data']['balance']);
    }

    public function testShow()
    {
        $client = static::createClient();

        $client->request('GET', '/api/users/1');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
    }

    public function testUpdate()
    {
        $client = static::createClient();

        $data = [
            'name' => 'Updated',
            'surname' => 'User',
            'personalCode' => '123456',
            'phoneNumber' => '1234567890',
            'dateOfBirth' => '2000-01-01'
        ];

        $client->request('PUT', '/api/users/1', [], [], [], json_encode($data));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        $this->assertEquals('Updated', $responseData['data']['name']);
        $this->assertEquals('User', $responseData['data']['surname']);
        $this->assertEquals('123456', $responseData['data']['personalCode']);
        $this->assertEquals('1234567890', $responseData['data']['phoneNumber']);
        $this->assertEquals('2000-01-01', $responseData['data']['dateOfBirth']);
    }

    public function testDelete()
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/users/1');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('User marked as deleted with id 1', $responseData['message']);
    }

    public function testTransfer()
    {
        $client = static::createClient();

        $data = [
            'fromUserId' => 1,
            'toUserId' => 2,
            'amount' => 5
        ];

        $client->request('POST', '/api/users/transfer', [], [], [], json_encode($data));

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Transfer successful', $responseData['message']);
    }
}
