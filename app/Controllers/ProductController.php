<?php

namespace App\Controllers;

use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProductController extends BaseController
{
    /**
     * Liste tous les produits
     * Supporte le filtre ?categorie=fruits via query string
     */
    public function index(): ResponseInterface
    {
        $model     = new ProductModel();
        $categorie = $this->request->getGet('categorie');

        $products = $categorie
            ? $model->where('categorie', $categorie)->findAll()
            : $model->findAll();

        return $this->response->setJSON($products);
    }

    /**
     * Retourne un produit par son ID
     */
    public function show(int $id): ResponseInterface
    {
        $model   = new ProductModel();
        $product = $model->find($id);

        if (!$product) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Produit non trouvé']);
        }

        return $this->response->setJSON($product);
    }

    /**
     * Crée un nouveau produit
     */
    public function create(): ResponseInterface
    {
        $model = new ProductModel();
        $data  = $this->request->getJSON(true) ?? [];

        if (empty($data['nom']) || !isset($data['prix']) || empty($data['categorie'])) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'nom, prix et categorie sont obligatoires']);
        }

        $id = $model->insert($data);

        if ($id === false) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => $model->errors()]);
        }

        return $this->response->setStatusCode(201)
            ->setJSON($model->find($id));
    }

    /**
     * Met à jour un produit existant
     */
    public function update(int $id): ResponseInterface
    {
        $model = new ProductModel();

        if (!$model->find($id)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Produit non trouvé']);
        }

        $data = $this->request->getJSON(true) ?? [];
        $model->update($id, $data);

        return $this->response->setJSON($model->find($id));
    }

    /**
     * Supprime un produit
     */
    public function delete(int $id): ResponseInterface
    {
        $model = new ProductModel();

        if (!$model->find($id)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Produit non trouvé']);
        }

        $model->delete($id);
        return $this->response->setStatusCode(204);
    }
}
