<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Page d'accueil ──────────────────────────────────────────
$routes->get('/', 'Home::index');

// ── Monitoring ──────────────────────────────────────────────
$routes->get('/health',  'Home::health');
$routes->get('/metrics', 'Home::metrics');

// ── Auth (public) ────────────────────────────────────────────
$routes->post('api/auth/register', 'AuthController::register');
$routes->post('api/auth/login',    'AuthController::login');

// ── Catégories (public) ──────────────────────────────────────
$routes->get('api/categories', 'CategoryController::index');

// ── Produits — lecture publique ──────────────────────────────
$routes->get('api/products',          'ProductController::index');
$routes->get('api/products/(:num)',   'ProductController::show/$1');

// ── Produits — écriture admin (JWT requis + rôle admin) ──────
$routes->post('api/products',         'ProductController::create',   ['filter' => 'jwt:admin']);
$routes->put('api/products/(:num)',   'ProductController::update/$1', ['filter' => 'jwt:admin']);
$routes->delete('api/products/(:num)','ProductController::delete/$1', ['filter' => 'jwt:admin']);

// ── Commandes (JWT requis) ───────────────────────────────────
$routes->post('api/orders',         'OrderController::create',   ['filter' => 'jwt']);
$routes->get('api/orders',          'OrderController::index',    ['filter' => 'jwt']);
$routes->get('api/orders/(:num)',   'OrderController::show/$1',  ['filter' => 'jwt']);
