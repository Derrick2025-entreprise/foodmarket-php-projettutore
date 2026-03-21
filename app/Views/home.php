<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'FoodMarket') ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1   { color: #2e7d32; }
        .endpoints { background: #f5f5f5; padding: 15px; border-radius: 8px; }
        code { background: #e8f5e9; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🛒 FoodMarket API</h1>
    <p>Bienvenue sur l'API e-commerce alimentaire.</p>

    <div class="endpoints">
        <h3>Endpoints disponibles :</h3>
        <ul>
            <li><code>GET  /health</code> — Statut de l'application</li>
            <li><code>GET  /products</code> — Liste des produits</li>
            <li><code>GET  /products/{id}</code> — Détail d'un produit</li>
            <li><code>POST /products</code> — Créer un produit</li>
            <li><code>PUT  /products/{id}</code> — Modifier un produit</li>
            <li><code>DELETE /products/{id}</code> — Supprimer un produit</li>
            <li><code>GET  /metrics</code> — Métriques Prometheus</li>
        </ul>
    </div>
</body>
</html>
