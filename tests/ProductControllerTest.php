<?php

/**
 * @file ProductControllerTest.php
 * @description Tests unitaires pour ProductController
 *
 * On utilise le trait DatabaseMigrations de CI4 pour :
 * - Créer les tables en SQLite (mémoire) avant chaque test
 * - Les supprimer après chaque test → isolation totale
 *
 * Pas besoin de MySQL, tout tourne en mémoire.
 */

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use App\Database\Seeds\ProductSeeder;

class ProductControllerTest extends CIUnitTestCase
{
    // Lance les migrations avant chaque test
    use DatabaseTestTrait;

    // Permet de simuler des requêtes HTTP complètes
    use FeatureTestTrait;

    // Utilise la connexion "tests" (SQLite en mémoire)
    protected $DBGroup = 'tests';

    // Exécute les migrations avant les tests
    protected $migrate = true;

    // Charge des données de test avant chaque test
    protected $seed = ProductSeeder::class;

    // ─────────────────────────────────────────────────────────
    // Tests GET /products
    // ─────────────────────────────────────────────────────────

    /**
     * GET /products doit retourner HTTP 200 et un tableau JSON
     */
    public function testGetAllProductsReturns200(): void
    {
        $result = $this->get('products');

        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $body = json_decode($result->getBody(), true);
        $this->assertIsArray($body);
        $this->assertNotEmpty($body);
    }

    /**
     * GET /products?categorie=fruits doit filtrer par catégorie
     */
    public function testGetProductsFilterByCategorie(): void
    {
        $result = $this->get('products?categorie=fruits');

        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);

        // Tous les résultats doivent être de la catégorie "fruits"
        foreach ($body as $product) {
            $this->assertEquals('fruits', $product['categorie']);
        }
    }

    // ─────────────────────────────────────────────────────────
    // Tests GET /products/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * GET /products/1 doit retourner le produit avec id=1
     */
    public function testGetProductByIdReturns200(): void
    {
        $result = $this->get('products/1');

        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertEquals(1, $body['id']);
    }

    /**
     * GET /products/9999 doit retourner HTTP 404
     */
    public function testGetProductByIdReturns404WhenNotFound(): void
    {
        $result = $this->get('products/9999');

        $result->assertStatus(404);
        $body = json_decode($result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    // ─────────────────────────────────────────────────────────
    // Tests POST /products
    // ─────────────────────────────────────────────────────────

    /**
     * POST /products avec données valides doit créer un produit (HTTP 201)
     */
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

    /**
     * POST /products sans champs obligatoires doit retourner HTTP 400
     */
    public function testCreateProductReturns400WhenMissingFields(): void
    {
        $result = $this->withBodyFormat('json')
            ->post('products', [
                'nom' => 'Produit incomplet',
                // prix et categorie manquants
            ]);

        $result->assertStatus(400);
        $body = json_decode($result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    // ─────────────────────────────────────────────────────────
    // Tests PUT /products/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * PUT /products/1 doit mettre à jour le produit
     */
    public function testUpdateProductReturns200(): void
    {
        $result = $this->withBodyFormat('json')
            ->put('products/1', ['prix' => 9.99]);

        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertEquals('9.99', $body['prix']);
    }

    /**
     * PUT /products/9999 doit retourner HTTP 404
     */
    public function testUpdateProductReturns404WhenNotFound(): void
    {
        $result = $this->withBodyFormat('json')
            ->put('products/9999', ['prix' => 1.00]);

        $result->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────
    // Tests DELETE /products/{id}
    // ─────────────────────────────────────────────────────────

    /**
     * DELETE /products/1 doit supprimer le produit (HTTP 204)
     */
    public function testDeleteProductReturns204(): void
    {
        $result = $this->delete('products/1');
        $result->assertStatus(204);

        // Vérifier que le produit n'existe plus
        $check = $this->get('products/1');
        $check->assertStatus(404);
    }

    /**
     * DELETE /products/9999 doit retourner HTTP 404
     */
    public function testDeleteProductReturns404WhenNotFound(): void
    {
        $result = $this->delete('products/9999');
        $result->assertStatus(404);
    }

    // ─────────────────────────────────────────────────────────
    // Test endpoint /health
    // ─────────────────────────────────────────────────────────

    /**
     * GET /health doit retourner {"status":"ok"}
     */
    public function testHealthEndpointReturnsOk(): void
    {
        $result = $this->get('health');

        $result->assertStatus(200);
        $body = json_decode($result->getBody(), true);
        $this->assertEquals('ok', $body['status']);
    }
}
