<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\ProductModel;

class ProductModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = 'App';

    private ProductModel $model;

    protected function setUp(): void
    {
        // Supprimer le fichier SQLite pour repartir d'un état propre
        $dbFile = '/tmp/test_foodmarket.db';
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

        $this->model = new ProductModel();
    }

    public function testInsertValidProduct(): void
    {
        $id = $this->model->insert([
            'nom'       => 'Bananes',
            'prix'      => 1.80,
            'categorie' => 'fruits',
            'stock'     => 100,
        ]);

        $this->assertNotFalse($id);
        $product = $this->model->find($id);
        $this->assertEquals('Bananes', $product['nom']);
    }

    public function testInsertFailsWithNegativePrice(): void
    {
        $result = $this->model->insert([
            'nom'       => 'Produit invalide',
            'prix'      => -5.00,
            'categorie' => 'fruits',
        ]);

        $this->assertFalse($result);
        $this->assertNotEmpty($this->model->errors());
    }

    public function testInsertFailsWithInvalidCategorie(): void
    {
        $result = $this->model->insert([
            'nom'       => 'Produit invalide',
            'prix'      => 5.00,
            'categorie' => 'electronique',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('categorie', $this->model->errors());
    }

    public function testInsertFailsWithoutNom(): void
    {
        $result = $this->model->insert([
            'prix'      => 3.00,
            'categorie' => 'legumes',
        ]);

        $this->assertFalse($result);
        $this->assertArrayHasKey('nom', $this->model->errors());
    }

    public function testFindAllReturnsArray(): void
    {
        $products = $this->model->findAll();
        $this->assertIsArray($products);
    }
}
