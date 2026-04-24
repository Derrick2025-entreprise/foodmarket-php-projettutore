<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * CategoryController
 *
 * GET /api/categories → Liste toutes les catégories
 */
class CategoryController extends BaseController
{
    public function index(): ResponseInterface
    {
        $model      = new CategoryModel();
        $categories = $model->findAll();

        return $this->response->setJSON($categories);
    }
}
