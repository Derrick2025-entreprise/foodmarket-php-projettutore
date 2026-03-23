<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table      = 'products';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['category_id', 'name', 'description', 'price', 'stock', 'image'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[100]',
        'price' => 'required|decimal|greater_than[0]',
        'stock' => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'name'  => ['required' => 'Le nom du produit est obligatoire'],
        'price' => [
            'required'     => 'Le prix est obligatoire',
            'greater_than' => 'Le prix doit être positif',
        ],
    ];

    /**
     * Retourne les produits avec le nom de leur catégorie
     */
    public function getWithCategory(?int $categoryId = null): array
    {
        $builder = $this->db->table('products p')
            ->select('p.*, c.name as category_name')
            ->join('categories c', 'c.id = p.category_id', 'left');

        if ($categoryId) {
            $builder->where('p.category_id', $categoryId);
        }

        return $builder->get()->getResultArray();
    }
}
