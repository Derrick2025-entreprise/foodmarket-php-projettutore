# FoodMarket PHP — Plateforme E-commerce Alimentaire

> Projet e-commerce alimentaire avec PHP CodeIgniter 4, Docker, CI/CD GitHub Actions, Prometheus et Kubernetes.
>
> **Auteurs :** METANGMODONGMO Berssaine & NGAHA DJIEHA Derrick Cabrel

---

## Résultats du projet

| Domaine              | Résultat                  |
|----------------------|---------------------------|
| Tests unitaires      | 47/47 passés (100%)       |
| Tests fonctionnels   | 33/33 validés             |
| Score Lighthouse     | 92/100                    |
| Chargement page      | < 2 secondes              |
| Sécurité OWASP       | 0 vulnérabilité critique  |
| Taux de réussite     | ~95%                      |

---

## Prérequis

- Docker Desktop installé
- Git installé
- Compte GitHub (pour la pipeline CI/CD)

## Démarrage rapide

```bash
# 1. Cloner le projet
git clone https://github.com/Derrick2025-entreprise/foodmarket-php-projettutore.git
cd foodmarket-php

# 2. Copier le fichier d'environnement
cp .env.example .env

# 3. Lancer l'application
docker-compose up -d

# 4. Exécuter les migrations
docker-compose exec app php spark migrate

# 5. Charger les données de test
docker-compose exec app php spark db:seed ProductSeeder
```

L'application est accessible sur : http://localhost:80

## URLs utiles

| Service       | URL                        |
|---------------|----------------------------|
| Application   | http://localhost:80        |
| phpMyAdmin    | http://localhost:8080      |
| Prometheus    | http://localhost:9090      |
| Node Exporter | http://localhost:9100      |

## API REST — Endpoints

### Authentification
| Méthode | Route               | Description          | Auth |
|---------|---------------------|----------------------|------|
| POST    | `/api/auth/register`| Inscription          | Non  |
| POST    | `/api/auth/login`   | Connexion → JWT      | Non  |

### Produits
| Méthode | Route                  | Description           | Auth  |
|---------|------------------------|-----------------------|-------|
| GET     | `/api/products`        | Liste des produits    | Non   |
| GET     | `/api/products/{id}`   | Détail produit        | Non   |
| POST    | `/api/products`        | Créer un produit      | Admin |
| PUT     | `/api/products/{id}`   | Modifier un produit   | Admin |
| DELETE  | `/api/products/{id}`   | Supprimer un produit  | Admin |

### Catégories
| Méthode | Route                | Description           | Auth |
|---------|----------------------|-----------------------|------|
| GET     | `/api/categories`    | Liste des catégories  | Non  |

### Commandes
| Méthode | Route                | Description              | Auth  |
|---------|----------------------|--------------------------|-------|
| POST    | `/api/orders`        | Créer une commande        | User  |
| GET     | `/api/orders`        | Mes commandes             | User  |
| GET     | `/api/orders/{id}`   | Détail d'une commande     | User  |

### Monitoring
| Méthode | Route       | Description              |
|---------|-------------|--------------------------|
| GET     | `/health`   | Statut de l'application  |
| GET     | `/metrics`  | Métriques Prometheus     |

## Tests avec curl

```bash
# Register
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Jean Dupont","email":"jean@example.com","password":"secret123"}'

# Login
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"jean@example.com","password":"secret123"}'

# Liste produits
curl http://localhost/api/products

# Créer commande (avec token JWT)
curl -X POST http://localhost/api/orders \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -d '{"items":[{"product_id":1,"quantity":2}]}'
```

## Structure du projet

```
foodmarket-php/
├── app/
│   ├── Config/
│   │   ├── Routes.php       # Toutes les routes API
│   │   └── Filters.php      # Filtre JWT
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── OrderController.php
│   │   └── Home.php         # /health + /metrics
│   ├── Models/
│   │   ├── UserModel.php
│   │   ├── ProductModel.php
│   │   ├── CategoryModel.php
│   │   ├── OrderModel.php
│   │   └── OrderItemModel.php
│   ├── Filters/
│   │   └── JwtFilter.php    # Authentification JWT
│   └── Database/
│       ├── Migrations/      # 5 tables
│       └── Seeds/
├── tests/
├── .github/workflows/       # CI/CD
├── monitoring/              # Prometheus
├── k8s/                     # Kubernetes
├── Dockerfile
└── docker-compose.yml
```

## Commandes utiles

```bash
# Lancer les tests
docker-compose exec app composer test

# Voir les logs
docker-compose logs -f app

# Arrêter tout
docker-compose down

# Migrations
docker-compose exec app php spark migrate

# Rollback
docker-compose exec app php spark migrate:rollback
```

## Contexte & Problématique

Face aux commissions excessives des plateformes comme Uber Eats ou Jumia Food (20-30%), l'objectif était de créer une solution propriétaire, économique et adaptée au contexte camerounais, intégrant notamment le Mobile Money.

## Pipeline CI/CD

- Push sur n'importe quelle branche → lint + tests
- Push sur `main` → lint + tests + build Docker + déploiement staging
- Tag `v*.*.*` → déploiement production
