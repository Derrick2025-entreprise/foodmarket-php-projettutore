<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Tests fonctionnels du ProductController
 * SQLite fichier partagé via defaultGroup='tests' en mode testing
 */
class ProductControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private static $testDb;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Supprimer le fichier SQLite de test s'il existe (repartir propre)
        $dbFile = WRITEPATH . 'tests.db';
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }

        self::$testDb = \Config\Database::connect();

        self::$testDb->query('DROP TABLE IF EXISTS products');
        self::$testDb->query('DROP TABLE IF EXISTS categories');
        self::$testDb->query('
            CREATE TABLE categories (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                name        VARCHAR(100) NOT NULL,
                description TEXT,
                image       VARCHAR(255)
            )
        ');
        self::$testDb->query('
            CREATE TABLE products (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER,
                name        VARCHAR(100) NOT NULL,
                description TEXT,
                price       DECIMAL(10,2) NOT NULL,
                stock       INT DEFAULT 0,
                image       VARCHAR(255),
                created_at  DATETIME,
                updated_at  DATETIME
            )
        ');

        self::$testDb->table('categories')->insert(['name' => 'Fruits']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Recréer la table pour remettre l'AUTOINCREMENT à 1
        self::$testDb->query('DROP TABLE IF EXISTS products');
        self::$testDb->query('
            CREATE TABLE products (
                id          INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER,
                name        VARCHAR(100) NOT NULL,
                description TEXT,
                price       DECIMAL(10,2) NOT NULL,
                stock       INT DEFAULT 0,
                image       VARCHAR(255),
                created_at  DATETIME,
                updated_at  DATETIME
            )
        ');
        self::$testDb->table('products')->insertBatch([
            ['name' => 'Pommes Bio',     'price' => 2.50,  'category_id' => 1, 'stock' => 100],
            ['name' => 'Carottes',       'price' => 1.20,  'category_id' => 1, 'stock' => 200],
            ['name' => 'Poulet fermier', 'price' => 12.00, 'category_id' => 1, 'stock' => 30],
            ['name' => 'Saumon frais',   'price' => 18.50, 'category_id' => 1, 'stock' => 20],
            ['name' => "Jus d'orange",   'price' => 3.00,  'category_id' => 1, 'stock' => 150],
        ]);
    }

    // Helper : parse le JSON depuis la réponse CI4
    private function json(\CodeIgniter\Test\TestResponse $result): ?array
    {
        $body = $result->response()->getBody();
        return json_decode($body, true);
    }

    public function testGetAllProductsReturns200(): void
    {
        $result = $this->get('api/products');
        $result->assertStatus(200);
        $body = $this->json($result);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body);
    }

    public function testGetProductsFilterByCategoryId(): void
    {
        $result = $this->get('api/products?category_id=1');
        $result->assertStatus(200);
        $body = $this->json($result);
        $this->assertIsArray($body);
        foreach ($body as $product) {
            $this->assertEquals(1, $product['category_id']);
        }
    }

    public function testGetProductByIdReturns200(): void
    {
        $result = $this->get('api/products/1');
        $result->assertStatus(200);
        $body = $this->json($result);
        $this->assertEquals(1, $body['id']);
    }

    public function testGetProductByIdReturns404WhenNotFound(): void
    {
        $result = $this->get('api/products/9999');
        $result->assertStatus(404);
        $body = $this->json($result);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
    }

    public function testCreateProductReturns201(): void
    {
        $result = $this->withBodyFormat('json')->post('api/products', [
            'name' => 'Mangues', 'price' => 4.50, 'category_id' => 1, 'stock' => 50,
        ]);
        $result->assertStatus(201);
        $body = $this->json($result);
        $this->assertEquals('Mangues', $body['name']);
        $this->assertArrayHasKey('id', $body);
    }

    public function testCreateProductReturns400WhenMissingFields(): void
    {
        $result = $this->withBodyFormat('json')->post('api/products', ['name' => 'Incomplet']);
        $result->assertStatus(400);
        $body = $this->json($result);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('errors', $body);
    }

    public function testUpdateProductReturns200(): void
    {
        $result = $this->withBodyFormat('json')->put('api/products/1', ['price' => 9.99]);
        $result->assertStatus(200);
        $body = $this->json($result);
        $this->assertEquals('9.99', $body['price']);
    }

    public function testUpdateProductReturns404WhenNotFound(): void
    {
        $result = $this->withBodyFormat('json')->put('api/products/9999', ['price' => 1.00]);
        $result->assertStatus(404);
    }

    public function testDeleteProductReturns204(): void
    {
        $result = $this->delete('api/products/1');
        $result->assertStatus(204);
        $this->get('api/products/1')->assertStatus(404);
    }

    public function testDeleteProductReturns404WhenNotFound(): void
    {
        $result = $this->delete('api/products/9999');
        $result->assertStatus(404);
    }

    public function testHealthEndpointReturnsOk(): void
    {
        $result = $this->get('health');
        $result->assertStatus(200);
        $body = $this->json($result);
        $this->assertIsArray($body);
        $this->assertEquals('ok', $body['status']);
    }
}
