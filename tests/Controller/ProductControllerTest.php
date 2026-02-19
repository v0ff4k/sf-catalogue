<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testCreateProduct(): void
    {
        $this->client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Product',
                'price' => '99.99',
                'status' => 'active'
            ])
        );

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Test Product', $response['name']);
        $this->assertEquals('99.99', $response['price']);
        $this->assertEquals('active', $response['status']);
    }

    public function testGetProduct(): void
    {
        $productId = $this->createTestProduct();

        $this->client->request('GET', '/api/products/' . $productId);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($productId, $response['id']);
        $this->assertEquals('Test Product', $response['name']);
    }

    public function testUpdateProduct(): void
    {
        $productId = $this->createTestProduct();

        $this->client->request(
            'PUT',
            '/api/products/' . $productId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Updated Product',
                'price' => '149.99',
                'status' => 'inactive'
            ])
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Updated Product', $response['name']);
        $this->assertEquals('149.99', $response['price']);
        $this->assertEquals('inactive', $response['status']);
    }

    public function testDeleteProduct(): void
    {
        $productId = $this->createTestProduct();

        $this->client->request('DELETE', '/api/products/' . $productId);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/api/products/' . $productId);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testGetNonExistentProduct(): void
    {
        $this->client->request('GET', '/api/products/99999');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    private function createTestProduct(): int
    {
        $this->client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Product',
                'price' => '99.99',
                'status' => 'active'
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['id'];
    }
}
