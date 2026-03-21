<?php

/**
 * @file ProductController.php
 * @description Contrôleur CRUD pour les produits alimentaires
 *
 * Routes :
 *   GET    /products           - Liste tous les produits
 *   GET    /products/{id}      - Détail d'un produit
 *   POST   /products           - Crée un produit (JSON)
 *   PUT    /products/{id}      - Met à jour un produit (JSON)
 *   DELETE /products/{id}      - Supprime un produit
 */

namespace App\Controllers;

use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProductController extends BaseController
{
    protected ProductModel $model;

    public function __construct()
    {
        $this->model = new ProductModel();
    }

    /**
     * Liste tous les produits
     * Supporte le filtre ?categorie=fruits via query string
     */
    public function index(): ResponseInterface
    {
        $categorie = $this->request->getGet('categorie');

        if ($categorie) {
            $products = $this->model->where('categorie', $categorie)->findAll();
        } else {
            $products = $this->model->findAll();
        }

        return $this->response->setJSON($products);
    }

    /**
     * Retourne un produit par son ID
     */
    public function show(int $id): ResponseInterface
    {
        $product = $this->model->find($id);

        if (!$product) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Produit non trouvé']);
        }

        return $this->response->setJSON($product);
    }

    /**
     * Crée un nouveau produit
     * Body JSON attendu : { nom, prix, categorie, stock }
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        // Validation des champs obligatoires
        if (!isset($data['nom'], $data['prix'], $data['categorie'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'nom, prix et categorie sont obligatoires']);
        }

        $id = $this->model->insert($data);

        return $this->response->setStatusCode(201)
            ->setJSON($this->model->find($id));
    }

    /**
     * Met à jour un produit existant
     */
    public function update(int $id): ResponseInterface
    {
        if (!$this->model->find($id)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Produit non trouvé']);
        }

        $data = $this->request->getJSON(true);
        $this->model->update($id, $data);

        return $this->response->setJSON($this->model->find($id));
    }

    /**
     * Supprime un produit
     */
    public function delete(int $id): ResponseInterface
    {
        if (!$this->model->find($id)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Produit non trouvé']);
        }

        $this->model->delete($id);
        return $this->response->setStatusCode(204);
    }
}
