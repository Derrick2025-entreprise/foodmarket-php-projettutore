<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ProductModel;

class ProductModelTest extends CIUnitTestCase
{
    private ProductModel $model;
    private static $db;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$db = \Config\Database::connect('tests');
        self::$db->query('DROP TABLE IF EXISTS products');
        self::$db->query('
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

    protected function setUp(): void
    {
        parent::setUp();
        self::$db->query('DELETE FROM products');
        $this->model = new ProductModel();
    }

    public function testInsertValidProduct(): void
    {
        $id = $this->model->insert([
            'nom' => 'Bananes', 'prix' => 1.80, 'categorie' => 'fruits', 'stock' => 100,
        ]);
        $this->assertNotFalse($id);
        $this->assertEquals('Bananes', $this->model->find($id)['nom']);
    }

    public function testInsertFailsWithNegativePrice(): void
    {
        $result = $this->model->insert([
            'nom' => 'Produit invalide', 'prix' => -5.00, 'categorie' => 'fruits',
        ]);
        $this->assertFalse($result);
        $this->assertNotEmpty($this->model->errors());
    }

    public function testInsertFailsWithInvalidCategorie(): void
    {
        $result = $this->model->insert([
            'nom' => 'Produit invalide', 'prix' => 5.00, 'categorie' => 'electronique',
        ]);
        $this->assertFalse($result);
        $this->assertArrayHasKey('categorie', $this->model->errors());
    }

    public function testInsertFailsWithoutNom(): void
    {
        $result = $this->model->insert(['prix' => 3.00, 'categorie' => 'legumes']);
        $this->assertFalse($result);
        $this->assertArrayHasKey('nom', $this->model->errors());
    }

    public function testFindAllReturnsArray(): void
    {
        $this->assertIsArray($this->model->findAll());
    }
}
