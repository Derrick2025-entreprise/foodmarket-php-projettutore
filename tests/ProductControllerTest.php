<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Database\Seeds\ProductSeeder;

class ProductControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = 'App';
    protected $seed      = ProductSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        $db = \Config\Database::connect('tests');
        $db->query('DROP TABLE IF EXISTS products');
        $db->query('
            CREATE TABLE products (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                nom         VARCHAR(100) NOT NULL,
                prix        DECIMAL(10,2) NOT NULL,
                categorie   VARCHAR(50) NOT NULL,
                stock       INT DEFAULT 0,
                description TEXT,
                image_url   VARCHAR(255),
                created_at  DATETIME,
                updated_at  DATETIME
            )
        ');
    }

    public function testGetAllProductsReturns200(): void
    {
        $result = $this->get('products');
        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body);
    }

    public function testGetProductsFilterByCategorie(): void
    {
        $result = $this->get('products?categorie=fruits');
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        foreach ($body as $product) {
            $this->assertEquals('fruits', $product['categorie']);
        }
    }

    public function testGetProductByIdReturns200(): void
    {
        $result = $this->get('products/1');
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertEquals(1, $body['id']);
    }

    public function testGetProductByIdReturns404WhenNotFound(): void
    {
        $result = $this->get('products/9999');
        $result->assertStatus(404);
        $body = json_decode($result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCreateProductReturns201(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('products', [
                'nom'       => 'Mangues',
                'prix'      => 4.50,
                'categorie' => 'fruits',
                'stock'     => 50,
            ]);
        $result->assertStatus(201);
        $body = json_decode($result->getBody(), true);
        $this->assertEquals('Mangues', $body['nom']);
        $this->assertArrayHasKey('id', $body);
    }

    public function testCreateProductReturns400WhenMissingFields(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('products', ['nom' => 'Incomplet']);
        $result->assertStatus(400);
        $body = json_decode($result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testUpdateProductReturns200(): void
    {
        $result = $this->withBodyFormat('json')
            ->put('products/1', ['prix' => 9.99]);
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertEquals('9.99', $body['prix']);
    }

    public function testUpdateProductReturns404WhenNotFound(): void
    {
        $result = $this->withBodyFormat('json')
            ->put('products/9999', ['prix' => 1.00]);
        $result->assertStatus(404);
    }

    public function testDeleteProductReturns204(): void
    {
        $result = $this->delete('products/1');
        $result->assertStatus(204);
        $check = $this->get('products/1');
        $check->assertStatus(404);
    }

    public function testDeleteProductReturns404WhenNotFound(): void
    {
        $result = $this->delete('products/9999');
        $result->assertStatus(404);
    }

    public function testHealthEndpointReturnsOk(): void
    {
        $result = $this->get('health');
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertEquals('ok', $body['status']);
    }
}
