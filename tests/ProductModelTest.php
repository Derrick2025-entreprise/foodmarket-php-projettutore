<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\ProductModel;

class ProductModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $DBGroup            = 'tests';
    protected $migrate            = true;
    protected $refresh            = true;
    protected $migrationNamespace = 'App';

    private ProductModel $model;

    protected function setUp(): void
    {
        parent::setUp();
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
