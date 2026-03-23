<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table      = 'order_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['order_id', 'product_id', 'quantity', 'price'];

    protected $validationRules = [
        'order_id'   => 'required|integer',
        'product_id' => 'required|integer',
        'quantity'   => 'required|integer|greater_than[0]',
        'price'      => 'required|decimal',
    ];

    /**
     * Retourne les items d'une commande avec les infos produit
     */
    public function getItemsWithProducts(int $orderId): array
    {
        return $this->db->table('order_items oi')
            ->select('oi.*, p.name as product_name, p.image')
            ->join('products p', 'p.id = oi.product_id')
            ->where('oi.order_id', $orderId)
            ->get()
            ->getResultArray();
    }
}
