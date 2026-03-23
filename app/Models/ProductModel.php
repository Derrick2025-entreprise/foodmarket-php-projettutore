<?php

/**
 * @file ProductModel.php
 * @description Modèle pour la table `products`
 * Gère la validation et les règles métier des produits alimentaires
 */

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    // Nom de la table en base de données
    protected $table = 'products';

    // Clé primaire
    protected $primaryKey = 'id';

    // Groupe de base de données — utilise 'tests' en environnement de test
    protected $DBGroup = 'default';

    // Retourne des tableaux associatifs (plus simple pour l'API JSON)
    protected $returnType = 'array';

    // Colonnes autorisées à l'insertion/mise à jour (protection contre mass assignment)
    protected $allowedFields = ['nom', 'prix', 'categorie', 'stock', 'description', 'image_url'];

    // Horodatage automatique (created_at, updated_at)
    protected $useTimestamps = true;

    // ── Règles de validation ────────────────────────────────────
    protected $validationRules = [
        'nom'       => 'required|min_length[2]|max_length[100]',
        'prix'      => 'required|decimal|greater_than[0]',
        'categorie' => 'required|in_list[fruits,legumes,viandes,poissons,boissons,autres]',
        'stock'     => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'nom'       => ['required' => 'Le nom du produit est obligatoire'],
        'prix'      => ['required' => 'Le prix est obligatoire', 'greater_than' => 'Le prix doit être positif'],
        'categorie' => ['in_list'  => 'Catégorie invalide'],
    ];
}
