<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table      = 'orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['user_id', 'total', 'status'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'user_id' => 'required|integer',
        'total'   => 'required|decimal',
    ];

    /**
     * Retourne les commandes d'un utilisateur avec ses items
     */
    public function getOrdersByUser(int $userId): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Retourne une commande avec ses items et produits associés
     */
    public function getOrderWithItems(int $orderId): ?array
    {
        $order = $this->find($orderId);
        if (!$order) {
            return null;
        }

        $itemModel    = new OrderItemModel();
        $order['items'] = $itemModel->getItemsWithProducts($orderId);

        return $order;
    }
}
