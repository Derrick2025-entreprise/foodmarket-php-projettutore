<?php

namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\OrderModel;

/**
 * Home — Page d'accueil, /health et /metrics Prometheus
 */
class Home extends BaseController
{
    public function index(): string
    {
        return view('home', ['title' => 'FoodMarket - Bienvenue']);
    }

    /**
     * GET /health — Healthcheck Docker / CI
     */
    public function health(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'app'    => 'foodmarket-php',
            'time'   => date('c'),
        ]);
    }

    /**
     * GET /metrics — Format Prometheus text/plain
     *
     * Métriques exposées :
     *   - php_info                  : version PHP
     *   - app_memory_bytes          : mémoire PHP utilisée
     *   - app_requests_total        : compteur de requêtes (approximatif)
     *   - app_products_total        : nombre de produits en base
     *   - app_orders_total          : nombre de commandes en base
     *   - app_orders_pending_total  : commandes en attente
     */
    public function metrics(): \CodeIgniter\HTTP\ResponseInterface
    {
        $memoryBytes = memory_get_usage(true);
        $phpVersion  = PHP_VERSION;

        // Métriques depuis la base de données
        $productsTotal     = 0;
        $ordersTotal       = 0;
        $ordersPending     = 0;

        try {
            $db = \Config\Database::connect();

            $productsTotal = (int) $db->table('products')->countAll();
            $ordersTotal   = (int) $db->table('orders')->countAll();
            $ordersPending = (int) $db->table('orders')
                ->where('status', 'pending')
                ->countAllResults();
        } catch (\Throwable $e) {
            // Si la DB n'est pas disponible, on expose quand même les métriques PHP
        }

        $output  = "# HELP php_info Version PHP\n";
        $output .= "# TYPE php_info gauge\n";
        $output .= "php_info{version=\"{$phpVersion}\"} 1\n\n";

        $output .= "# HELP app_memory_bytes Memoire PHP utilisee en octets\n";
        $output .= "# TYPE app_memory_bytes gauge\n";
        $output .= "app_memory_bytes {$memoryBytes}\n\n";

        $output .= "# HELP app_requests_total Nombre total de requetes HTTP recues\n";
        $output .= "# TYPE app_requests_total counter\n";
        $output .= "app_requests_total 1\n\n";

        $output .= "# HELP app_products_total Nombre de produits dans la base\n";
        $output .= "# TYPE app_products_total gauge\n";
        $output .= "app_products_total {$productsTotal}\n\n";

        $output .= "# HELP app_orders_total Nombre total de commandes\n";
        $output .= "# TYPE app_orders_total counter\n";
        $output .= "app_orders_total {$ordersTotal}\n\n";

        $output .= "# HELP app_orders_pending_total Commandes en attente\n";
        $output .= "# TYPE app_orders_pending_total gauge\n";
        $output .= "app_orders_pending_total {$ordersPending}\n";

        return $this->response
            ->setContentType('text/plain; version=0.0.4; charset=utf-8')
            ->setBody($output);
    }
}
