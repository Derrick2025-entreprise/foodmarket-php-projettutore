<?php

/**
 * @file Home.php
 * @description Contrôleur principal - page d'accueil du FoodMarket
 * Redirige vers la liste des produits
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
     */
    public function health(): \CodeIgniter\HTTP\ResponseInterface
    {
        return $this->response->setJSON(['status' => 'ok', 'app' => 'foodmarket-php']);
    }
}
