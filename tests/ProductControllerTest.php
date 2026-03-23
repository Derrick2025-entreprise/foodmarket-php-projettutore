<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

class ProductControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup = 'tests';
    protected $migrate = false;
    protected $refresh = false;

    protected function setUp(): void
    {
        // Supprimer le fichier SQLite pour repartir d'un état propre
        $dbFile = WRITEPATH . 'tests.db';
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }

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

        $db->table('products')->insertBatch([
            ['nom' => 'Pommes Bio',     'prix' => 2.50,  'categorie' => 'fruits',   'stock' => 100],
            ['nom' => 'Carottes',       'prix' => 1.20,  'categorie' => 'legumes',  'stock' => 200],
            ['nom' => 'Poulet fermier', 'prix' => 12.00, 'categorie' => 'viandes',  'stock' => 30],
            ['nom' => 'Saumon frais',   'prix' => 18.50, 'categorie' => 'poissons', 'stock' => 20],
            ['nom' => "Jus d'orange",   'prix' => 3.00,  'categorie' => 'boissons', 'stock' => 150],
        ]);
    }

    public function testGetAllProductsReturns200(): void
    {
        $result = $this->get('products');
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body);
    }

    public function testGetProductsFilterByCategorie(): void
    {
        $result = $this->get('products?categorie=fruits');
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
        foreach ($body as $product) {
            $this->assertEquals('fruits', $product['categorie']);
        }
    }

    public function testGetProductByIdReturns200(): void
    {
        $result = $this->get('products/1');
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertEquals(1, $body['id']);
    }

    public function testGetProductByIdReturns404WhenNotFound(): void
    {
        $result = $this->get('products/9999');
        $result->assertStatus(404);
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
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
        $this->assertIsArray($body);
        $this->assertEquals('Mangues', $body['nom']);
        $this->assertArrayHasKey('id', $body);
    }

    public function testCreateProductReturns400WhenMissingFields(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('products', ['nom' => 'Incomplet']);
        $result->assertStatus(400);
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
    }

    public function testUpdateProductReturns200(): void
    {
        $result = $this->withBodyFormat('json')
            ->put('products/1', ['prix' => 9.99]);
        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
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
        $this->assertIsArray($body);
        $this->assertEquals('ok', $body['status']);
    }
}
