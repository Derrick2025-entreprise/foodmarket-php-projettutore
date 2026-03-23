<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ProductModel;

/**
 * Tests unitaires du ProductModel
 * SQLite fichier partagé via defaultGroup='tests' en mode testing
 */
class ProductModelTest extends CIUnitTestCase
{
    private ProductModel $model;
    private static $testDb;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

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
        $this->model = new ProductModel();
    }

    public function testInsertValidProduct(): void
    {
        $id = $this->model->insert([
            'name' => 'Bananes', 'price' => 1.80, 'category_id' => 1, 'stock' => 100,
        ]);
        $this->assertNotFalse($id);
        $this->assertEquals('Bananes', $this->model->find($id)['name']);
    }

    public function testInsertFailsWithNegativePrice(): void
    {
        $result = $this->model->insert([
            'name' => 'Produit invalide', 'price' => -5.00, 'category_id' => 1,
        ]);
        $this->assertFalse($result);
        $this->assertNotEmpty($this->model->errors());
    }

    public function testInsertFailsWithoutName(): void
    {
        $result = $this->model->insert(['price' => 3.00, 'category_id' => 1]);
        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $this->model->errors());
    }

    public function testInsertFailsWithoutPrice(): void
    {
        $result = $this->model->insert(['name' => 'Produit sans prix', 'category_id' => 1]);
        $this->assertFalse($result);
        $this->assertArrayHasKey('price', $this->model->errors());
    }

    public function testFindAllReturnsArray(): void
    {
        $this->assertIsArray($this->model->findAll());
    }
}
