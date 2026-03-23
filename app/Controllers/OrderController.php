<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * OrderController — Gestion des commandes
 *
 * POST /api/orders          → Créer une commande (user authentifié)
 * GET  /api/orders          → Mes commandes
 * GET  /api/orders/{id}     → Détail d'une commande
 */
class OrderController extends BaseController
{
    /**
     * POST /api/orders
     * Body JSON : { "items": [{ "product_id": 1, "quantity": 2 }, ...] }
     */
    public function create(): ResponseInterface
    {
        $userId = $this->request->jwtPayload['user_id'] ?? null;

        if (!$userId) {
            return $this->response->setStatusCode(401)
                ->setJSON(['error' => 'Non authentifié']);
        }

        $data  = $this->request->getJSON(true) ?? [];
        $items = $data['items'] ?? [];

        if (empty($items)) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => 'La commande doit contenir au moins un article']);
        }

        $productModel   = new ProductModel();
        $orderModel     = new OrderModel();
        $orderItemModel = new OrderItemModel();

        // Calculer le total et vérifier le stock
        $total     = 0;
        $lineItems = [];

        foreach ($items as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                return $this->response->setStatusCode(400)
                    ->setJSON(['error' => 'Chaque article doit avoir product_id et quantity']);
            }

            $product = $productModel->find((int) $item['product_id']);

            if (!$product) {
                return $this->response->setStatusCode(404)
                    ->setJSON(['error' => "Produit #{$item['product_id']} introuvable"]);
            }

            if ($product['stock'] < (int) $item['quantity']) {
                return $this->response->setStatusCode(400)
                    ->setJSON(['error' => "Stock insuffisant pour {$product['name']}"]);
            }

            $lineTotal   = $product['price'] * (int) $item['quantity'];
            $total      += $lineTotal;
            $lineItems[] = [
                'product_id' => $product['id'],
                'quantity'   => (int) $item['quantity'],
                'price'      => $product['price'],
            ];
        }

        // Créer la commande
        $orderId = $orderModel->insert([
            'user_id' => $userId,
            'total'   => round($total, 2),
            'status'  => 'pending',
        ]);

        // Insérer les items et décrémenter le stock
        foreach ($lineItems as $line) {
            $line['order_id'] = $orderId;
            $orderItemModel->insert($line);

            // Décrémenter le stock
            $productModel->set('stock', "stock - {$line['quantity']}", false)
                         ->where('id', $line['product_id'])
                         ->update();
        }

        $order = $orderModel->getOrderWithItems($orderId);

        return $this->response->setStatusCode(201)->setJSON($order);
    }

    /**
     * GET /api/orders — Commandes de l'utilisateur connecté
     */
    public function index(): ResponseInterface
    {
        $userId = $this->request->jwtPayload['user_id'] ?? null;

        if (!$userId) {
            return $this->response->setStatusCode(401)
                ->setJSON(['error' => 'Non authentifié']);
        }

        $orderModel = new OrderModel();
        $orders     = $orderModel->getOrdersByUser((int) $userId);

        return $this->response->setJSON($orders);
    }

    /**
     * GET /api/orders/{id}
     */
    public function show(int $id): ResponseInterface
    {
        $userId = $this->request->jwtPayload['user_id'] ?? null;

        $orderModel = new OrderModel();
        $order      = $orderModel->getOrderWithItems($id);

        if (!$order) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => 'Commande non trouvée']);
        }

        // Un user ne peut voir que ses propres commandes (sauf admin)
        $role = $this->request->jwtPayload['role'] ?? 'user';
        if ($role !== 'admin' && $order['user_id'] != $userId) {
            return $this->response->setStatusCode(403)
                ->setJSON(['error' => 'Accès refusé']);
        }

        return $this->response->setJSON($order);
    }
}
