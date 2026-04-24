<?php

/**
 * @file ProductSeeder.php
 * @description Données de test pour la table products
 * Exécuter avec : php spark db:seed ProductSeeder
 */

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['nom' => 'Pommes Bio',      'prix' => 2.50,  'categorie' => 'fruits',   'stock' => 100, 'description' => 'Pommes biologiques locales'],
            ['nom' => 'Carottes',        'prix' => 1.20,  'categorie' => 'legumes',  'stock' => 200, 'description' => 'Carottes fraîches du marché'],
            ['nom' => 'Poulet fermier',  'prix' => 12.00, 'categorie' => 'viandes',  'stock' => 30,  'description' => 'Poulet élevé en plein air'],
            ['nom' => 'Saumon frais',    'prix' => 18.50, 'categorie' => 'poissons', 'stock' => 20,  'description' => 'Saumon atlantique frais'],
            ['nom' => 'Jus d\'orange',   'prix' => 3.00,  'categorie' => 'boissons', 'stock' => 150, 'description' => 'Jus pressé 100% naturel'],
        ];

        $this->db->table('products')->insertBatch($products);
    }
}
