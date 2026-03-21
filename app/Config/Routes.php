<?php

/**
 * @file Routes.php
 * @description Définition des routes de l'application FoodMarket
 */

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Page d'accueil ──────────────────────────────────────────
$routes->get('/', 'Home::index');

// ── Endpoint de santé (utilisé par Docker et le CI/CD) ──────
$routes->get('/health', 'Home::health');

// ── Endpoint métriques Prometheus ───────────────────────────
$routes->get('/metrics', 'Home::metrics');

// ── API Produits (RESTful) ───────────────────────────────────
$routes->get('products',          'ProductController::index');
$routes->get('products/(:num)',   'ProductController::show/$1');
$routes->post('products',         'ProductController::create');
$routes->put('products/(:num)',   'ProductController::update/$1');
$routes->delete('products/(:num)','ProductController::delete/$1');
