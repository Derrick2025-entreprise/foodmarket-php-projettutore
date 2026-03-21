# FoodMarket PHP — E-commerce Alimentaire

Projet CI/CD complet avec PHP CodeIgniter 4, Docker, GitHub Actions, Prometheus et Kubernetes.

## Prérequis

- Docker Desktop installé
- Git installé
- Compte GitHub (pour la pipeline CI/CD)

## Démarrage rapide

```bash
# 1. Cloner le projet
git clone https://github.com/TON_USERNAME/foodmarket-php.git
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

L'application est accessible sur : http://localhost:8080

## URLs utiles

| Service     | URL                        |
|-------------|----------------------------|
| Application | http://localhost:8080      |
| phpMyAdmin  | http://localhost:8081      |
| Prometheus  | http://localhost:9090      |
| Grafana     | http://localhost:3001      |
| Kibana      | http://localhost:5601      |

## Structure du projet

```
foodmarket-php/
├── app/
│   ├── Config/          # Configuration CI4
│   ├── Controllers/     # Contrôleurs HTTP
│   ├── Models/          # Modèles base de données
│   ├── Views/           # Templates HTML
│   └── Database/
│       ├── Migrations/  # Migrations SQL
│       └── Seeds/       # Données de test
├── tests/               # Tests PHPUnit
├── .github/workflows/   # Pipelines CI/CD
├── monitoring/          # Config Prometheus/Grafana
├── k8s/                 # Manifestes Kubernetes
├── Dockerfile
└── docker-compose.yml
```

## Commandes utiles

```bash
# Lancer les tests
docker-compose exec app composer test

# Vérifier la syntaxe PHP
docker-compose exec app composer lint

# Voir les logs
docker-compose logs -f app

# Arrêter tout
docker-compose down
```

## Pipeline CI/CD

- Push sur n'importe quelle branche → lint + tests
- Push sur `main` → lint + tests + build Docker + déploiement staging
- Tag `v*.*.*` → déploiement production
