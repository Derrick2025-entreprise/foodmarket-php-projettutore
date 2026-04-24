<?php

namespace App\Controllers;

use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ProductController — CRUD produits
 *
 * GET    /api/products          → Liste (filtre ?category_id=X)
 * GET    /api/products/{id}     → Détail
 * POST   /api/products          → Créer (admin)
 * PUT    /api/products/{id}     → Modifier (admin)
 * DELETE /api/products/{id}     → Supprimer (admin)
 */
class ProductController extends BaseController
{
    public function index(): ResponseInterface
    {
        $model      = new ProductModel();
        $categoryId = $this->request->getGet('category_id');

        $products = $model->getWithCategory($categoryId ? (int) $categoryId : null);

        return $this->response->setJSON($products);
    }

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

    public function create(): ResponseInterface
    {
        $model = new ProductModel();
        $data  = $this->request->getJSON(true) ?? [];

        if (!$model->insert($data)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['errors' => $model->errors()]);
        }

        return $this->response->setStatusCode(201)
            ->setJSON($model->find($model->getInsertID()));
    }

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

    public function delete(int $id): ResponseInterface
    {
        $model = new ProductModel();

        if (!$model->find($id)) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Produit non trouvé']);
        }

        $model->delete($id);

        return $this->response->setStatusCode(204)->setBody('');
    }
}
