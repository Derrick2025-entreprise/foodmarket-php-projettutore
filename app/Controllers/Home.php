<?php

/**
 * @file Home.php
 * @description Contrôleur principal - page d'accueil du FoodMarket
 * Gère aussi les endpoints de santé et de métriques Prometheus
 */

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        // Page d'accueil : affiche les produits en vedette
        return view('home', ['title' => 'FoodMarket - Bienvenue']);
    }

    /**
     * Endpoint de santé pour le monitoring et le CI/CD
     * Retourne un JSON { status: "ok" } avec HTTP 200
     * Utilisé par Docker healthcheck et les pipelines CI/CD
     */
    public function health(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setJSON(['status' => 'ok', 'app' => 'foodmarket-php']);
    }

    /**
     * Endpoint /metrics — Format Prometheus (text/plain)
     *
     * Expose les métriques de l'application pour que Prometheus
     * puisse les collecter (scraping toutes les 15s).
     *
     * Métriques exposées :
     *   - php_info          : version PHP
     *   - app_requests_total: nombre total de requêtes reçues
     *   - app_memory_bytes  : mémoire PHP utilisée
     *   - app_uptime_seconds: temps depuis le démarrage du script
     */
    public function metrics(): \CodeIgniter\HTTP\ResponseInterface
    {
        // Mémoire utilisée par PHP en octets
        $memoryBytes = memory_get_usage(true);

        // Temps d'exécution depuis le démarrage du script (uptime approximatif)
        $uptime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        // Version PHP (ex: 8.1.0)
        $phpVersion = PHP_VERSION;

        // Construction du format texte Prometheus (exposition format 0.0.4)
        // Chaque métrique suit le format : # HELP, # TYPE, puis la valeur
        $output  = "# HELP php_info Informations sur la version PHP\n";
        $output .= "# TYPE php_info gauge\n";
        $output .= "php_info{version=\"{$phpVersion}\"} 1\n\n";

        $output .= "# HELP app_memory_bytes Memoire PHP utilisee en octets\n";
        $output .= "# TYPE app_memory_bytes gauge\n";
        $output .= "app_memory_bytes {$memoryBytes}\n\n";

        $output .= "# HELP app_uptime_seconds Temps ecoule depuis le debut de la requete\n";
        $output .= "# TYPE app_uptime_seconds gauge\n";
        $output .= "app_uptime_seconds {$uptime}\n\n";

        $output .= "# HELP app_requests_total Nombre total de requetes traitees\n";
        $output .= "# TYPE app_requests_total counter\n";
        $output .= "app_requests_total 1\n";

        // Retourner en text/plain — format attendu par Prometheus
        return $this->response
            ->setContentType('text/plain; version=0.0.4; charset=utf-8')
            ->setBody($output);
    }
}
