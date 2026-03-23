# Rapport Technique — FoodMarket PHP
## Projet Tutoré CI/CD — DevOps

**Auteur :** Derrick  
**Date :** Mars 2026  
**Dépôt :** https://github.com/Derrick2025-entreprise/foodmarket-php-projettutore  
**Image Docker :** derrickdev/foodmarket-php

---

## 1. Introduction

Ce projet consiste à développer une application e-commerce alimentaire en PHP CodeIgniter 4,
puis à mettre en place une chaîne DevOps complète couvrant :

- La conteneurisation avec Docker
- L'intégration et le déploiement continus (CI/CD) via GitHub Actions
- Le monitoring en temps réel avec Prometheus et Grafana
- La centralisation des logs avec la stack ELK
- L'orchestration avec Kubernetes

L'objectif est de démontrer la maîtrise des pratiques DevOps modernes sur un projet concret.

---

## 2. Architecture du système

```
┌─────────────────────────────────────────────────────────────┐
│                        DÉVELOPPEUR                          │
│                    git push / git tag                       │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   GITHUB ACTIONS (CI/CD)                    │
│                                                             │
│  CI : lint → test → build → sonarqube → audit sécurité     │
│  CD : push image Docker Hub → deploy staging / production   │
└──────────┬──────────────────────────────┬───────────────────┘
           │                              │
           ▼                              ▼
┌──────────────────┐           ┌──────────────────────────────┐
│   DOCKER HUB     │           │     SERVEUR STAGING / PROD   │
│ derrickdev/      │──────────▶│  docker-compose up           │
│ foodmarket-php   │           │  php spark migrate           │
└──────────────────┘           └──────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    STACK APPLICATIVE                        │
│                                                             │
│  ┌──────────────┐  ┌──────────┐  ┌────────────────────┐   │
│  │  PHP App     │  │  MySQL   │  │    phpMyAdmin       │   │
│  │  :8080       │  │  :3306   │  │    :8081            │   │
│  └──────┬───────┘  └──────────┘  └────────────────────┘   │
│         │                                                   │
│  ┌──────▼───────────────────────────────────────────────┐  │
│  │              MONITORING & LOGS                        │  │
│  │  Prometheus:9090  Grafana:3001  Kibana:5601           │  │
│  │  Elasticsearch:9200  Logstash:5044  Filebeat          │  │
│  └──────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│                      KUBERNETES                             │
│                                                             │
│  Deployment (2 replicas) + HPA (CPU 70% / RAM 80%)         │
│  Service LoadBalancer + ClusterIP DB                        │
│  Secrets + PersistentVolume MySQL                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Application PHP CodeIgniter 4

### 3.1 Structure du projet

```
foodmarket-php/
├── app/
│   ├── Config/
│   │   ├── App.php          # Configuration générale
│   │   ├── Database.php     # Connexion MySQL / SQLite (tests)
│   │   ├── Logger.php       # Logs JSON pour ELK
│   │   └── Routes.php       # Définition des routes API
│   ├── Controllers/
│   │   ├── ProductController.php   # CRUD produits (JSON)
│   │   └── Home.php                # Page d'accueil + /health + /metrics
│   ├── Models/
│   │   └── ProductModel.php        # Modèle avec validation
│   └── Database/
│       ├── Migrations/             # Création table products
│       └── Seeds/                  # Données de test
├── tests/                          # Tests PHPUnit
├── .github/workflows/              # CI/CD GitHub Actions
├── monitoring/                     # Prometheus + Grafana
├── elk/                            # Logstash + Filebeat
├── k8s/                            # Manifestes Kubernetes
├── Dockerfile
└── docker-compose.yml
```

### 3.2 API REST — Endpoints

| Méthode | Route              | Description                  |
|---------|--------------------|------------------------------|
| GET     | `/`                | Page d'accueil HTML          |
| GET     | `/health`          | Statut de l'application      |
| GET     | `/metrics`         | Métriques Prometheus         |
| GET     | `/products`        | Liste tous les produits      |
| GET     | `/products/{id}`   | Détail d'un produit          |
| POST    | `/products`        | Créer un produit             |
| PUT     | `/products/{id}`   | Modifier un produit          |
| DELETE  | `/products/{id}`   | Supprimer un produit         |

### 3.3 Modèle de données

```sql
CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(255) NOT NULL,
    prix        DECIMAL(10,2) NOT NULL,
    categorie   VARCHAR(100) NOT NULL,
    stock       INT DEFAULT 0,
    description TEXT,
    image_url   VARCHAR(500),
    created_at  DATETIME,
    updated_at  DATETIME
);
```

---

## 4. Pipeline CI/CD

### 4.1 Intégration Continue (CI)

Le fichier `.github/workflows/ci.yml` définit 5 jobs exécutés à chaque push sur **toutes les branches** (`branches: ["**"]`) :

```
Push / PR
    │
    ├── lint ──────────────── Vérification syntaxe PHP (php -l)
    │
    ├── test (needs: lint) ── PHPUnit + couverture XDebug
    │       │
    │       ├── sonarqube ─── Analyse qualité SonarQube
    │       │
    │       └── build ──────── Construction image Docker
    │
    └── security ──────────── composer audit (vulnérabilités, tourne en parallèle)
```

**Job lint** : parcourt tous les fichiers `.php` avec `php -l` pour détecter les erreurs de syntaxe.

**Job test** : lance PHPUnit avec SQLite en mémoire (pas besoin de MySQL en CI), génère un rapport de couverture XDebug uploadé comme artefact.

**Job build** : construit l'image Docker avec cache GitHub Actions pour accélérer les builds suivants.

**Job sonarqube** : analyse statique du code, détecte les bugs, code smells et vulnérabilités.

**Job security** : `composer audit` vérifie les CVE connues dans les dépendances.

### 4.2 Déploiement Continu (CD)

Le fichier `.github/workflows/cd.yml` gère deux environnements :

**Staging** (push sur `main`) :
1. Build et push de l'image `derrickdev/foodmarket-php:latest` sur Docker Hub
2. Connexion SSH au serveur staging
3. `docker-compose pull && docker-compose up -d`
4. `php spark migrate --force`

**Production** (tag `v*.*.*`) :
1. Push de l'image taguée `derrickdev/foodmarket-php:v1.0.0`
2. Déploiement avec approbation manuelle (environnement protégé GitHub)
3. Mise à jour avec la version taguée + migrations

### 4.3 Secrets GitHub configurés

| Secret            | Usage                              |
|-------------------|------------------------------------|
| DOCKER_USERNAME   | Connexion Docker Hub (`derrickdev`)|
| DOCKER_PASSWORD   | Token Docker Hub                   |
| STAGING_HOST      | IP du serveur staging              |
| STAGING_USER      | Utilisateur SSH staging            |
| STAGING_SSH_KEY   | Clé privée SSH staging             |
| SONAR_TOKEN       | Token SonarQube                    |
| SONAR_HOST_URL    | URL instance SonarQube             |

---

## 5. Conteneurisation Docker

### 5.1 Dockerfile

L'image est basée sur `php:8.1-apache`, l'image officielle PHP intégrant Apache.

```dockerfile
FROM php:8.1-apache

# Extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev libzip-dev zip unzip git \
    && docker-php-ext-install intl mysqli pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Activation mod_rewrite (requis par CodeIgniter 4)
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data writable \
    && chmod -R 755 writable

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
```

Ce choix garantit une image officielle maintenue, avec toutes les extensions PHP installées explicitement.

### 5.2 docker-compose.yml — Services

| Service       | Image                    | Port  | Rôle                        |
|---------------|--------------------------|-------|-----------------------------|
| app           | build local              | 8080  | Application PHP             |
| db            | mysql:8.0                | 3306  | Base de données             |
| phpmyadmin    | phpmyadmin/phpmyadmin    | 8081  | Interface admin DB          |
| prometheus    | prom/prometheus          | 9090  | Collecte métriques          |
| grafana       | grafana/grafana          | 3001  | Visualisation métriques     |
| elasticsearch | elasticsearch:8.x        | 9200  | Indexation logs             |
| logstash      | logstash:8.x             | 5044  | Pipeline logs               |
| kibana        | kibana:8.x               | 5601  | Visualisation logs          |
| filebeat      | elastic/filebeat:8.x     | —     | Collecte logs fichiers      |
| node-exporter | prom/node-exporter       | 9100  | Métriques système           |

---

## 6. Monitoring — Prometheus & Grafana

### 6.1 Prometheus

La configuration `monitoring/prometheus.yml` définit deux cibles de scraping :

- **app** (`localhost:8080/metrics`) — métriques applicatives toutes les 15s
- **node-exporter** (`node-exporter:9100`) — métriques système (CPU, RAM, réseau)

L'endpoint `/metrics` de l'application retourne des métriques au format Prometheus :

```
# HELP http_requests_total Total HTTP requests
# TYPE http_requests_total counter
http_requests_total{method="GET",status="200"} 42
http_response_time_seconds{quantile="0.99"} 0.023
```

### 6.2 Grafana — Dashboard

Le dashboard `monitoring/grafana-dashboard.json` contient 4 panneaux :

1. **Requêtes HTTP/s** — taux de requêtes en temps réel
2. **Temps de réponse** — percentile 95 et 99
3. **Utilisation CPU** — pourcentage par core
4. **Utilisation mémoire** — RAM utilisée vs disponible

### 6.3 Alertes Grafana

Le fichier `monitoring/grafana-alerting.yml` définit deux alertes :

| Alerte              | Condition                    | Sévérité |
|---------------------|------------------------------|----------|
| MemoryHighUsage     | RAM > 80% pendant 5 min      | warning  |
| AppDown             | App inaccessible > 1 min     | critical |

---

## 7. Centralisation des logs — ELK Stack

### 7.1 Architecture ELK

```
Application PHP
    │ (logs JSON dans writable/logs/)
    ▼
Filebeat (collecte les fichiers de logs)
    │
    ▼
Logstash (parse, filtre, enrichit)
    │
    ▼
Elasticsearch (indexe et stocke)
    │
    ▼
Kibana (visualise et filtre)
```

### 7.2 Format des logs JSON

CodeIgniter 4 est configuré dans `app/Config/Logger.php` pour écrire des logs JSON :

```json
{
  "timestamp": "2026-03-21T10:30:00Z",
  "level": "ERROR",
  "message": "Product not found",
  "context": {
    "product_id": 42,
    "url": "/products/42",
    "ip": "192.168.1.1"
  }
}
```

### 7.3 Pipeline Logstash

Le fichier `elk/logstash/pipeline/logstash.conf` :
- **Input** : Filebeat sur le port 5044
- **Filter** : parse JSON, ajoute timestamp, extrait le niveau de log
- **Output** : Elasticsearch avec index `foodmarket-logs-YYYY.MM.DD`

---

## 7. Stratégie de tests

### 7.1 Architecture des tests

Les tests sont écrits avec PHPUnit et utilisent SQLite en mémoire pour s'affranchir de toute dépendance à MySQL.

| Fichier de test                    | Classe                   | Nombre de tests | Couverture                          |
|------------------------------------|--------------------------|-----------------|-------------------------------------|
| tests/ProductControllerTest.php    | ProductControllerTest    | 9 tests         | Routes, réponses HTTP, codes statut |
| tests/ProductModelTest.php         | ProductModelTest         | 5 tests         | Validation des champs du modèle     |

### 7.2 ProductControllerTest — 9 tests

| Méthode de test                          | Endpoint              | Scénario testé                        |
|------------------------------------------|-----------------------|---------------------------------------|
| testGetAllProductsReturns200()           | GET /products         | Retourne status 200 + tableau JSON    |
| testGetProductsFilterByCategorie()       | GET /products?cat=X   | Filtre par catégorie fonctionne       |
| testGetProductByIdReturns200()           | GET /products/{id}    | Retourne status 200 + objet JSON      |
| testGetProductByIdReturns404WhenNotFound()| GET /products/9999   | Retourne status 404                   |
| testCreateProductReturns201()            | POST /products        | Création valide — status 201          |
| testCreateProductReturns400WhenMissingFields() | POST /products  | Données invalides — status 400        |
| testUpdateProductReturns200()            | PUT /products/{id}    | Modification — status 200             |
| testUpdateProductReturns404WhenNotFound()| PUT /products/9999    | Produit inexistant — status 404       |
| testDeleteProductReturns204()            | DELETE /products/{id} | Suppression — status 204              |
| testDeleteProductReturns404WhenNotFound()| DELETE /products/9999 | Produit inexistant — status 404       |
| testHealthEndpointReturnsOk()            | GET /health           | Status 200 + {status: ok}             |

### 7.3 ProductModelTest — 5 tests

| Méthode de test                      | Validation testée                              |
|--------------------------------------|------------------------------------------------|
| testInsertValidProduct()             | Insertion valide — retourne un ID              |
| testInsertFailsWithNegativePrice()   | Prix négatif — validation échoue              |
| testInsertFailsWithInvalidCategorie()| Catégorie non autorisée — validation échoue   |
| testInsertFailsWithoutNom()          | Nom absent — validation échoue                |
| testFindAllReturnsArray()            | findAll() retourne un tableau                  |

---

## 8. Kubernetes

### 8.1 Manifestes déployés

| Fichier              | Ressource                    | Description                        |
|----------------------|------------------------------|------------------------------------|
| `deployment.yml`     | Deployment                   | 2 replicas, Rolling Update         |
| `service.yml`        | Service LoadBalancer         | Exposition externe port 80         |
| `hpa.yml`            | HorizontalPodAutoscaler      | Scale CPU>70% ou RAM>80%           |
| `db-deployment.yml`  | Deployment + PVC             | MySQL avec stockage persistant     |
| `secrets.yml`        | Secret                       | Credentials DB encodés base64      |

### 8.2 Stratégie de déploiement

```yaml
strategy:
  type: RollingUpdate
  rollingUpdate:
    maxSurge: 1        # 1 pod supplémentaire pendant la mise à jour
    maxUnavailable: 0  # Aucun pod indisponible → zéro downtime
```

### 8.3 Auto-scaling (HPA)

```yaml
minReplicas: 2
maxReplicas: 10
metrics:
  - CPU    → scale si > 70%
  - Memory → scale si > 80%
```

---

## 9. Sécurité et qualité du code

### 9.1 SonarQube

Intégré dans la pipeline CI, SonarQube analyse :
- Les bugs et code smells
- Les vulnérabilités de sécurité (OWASP)
- La couverture de tests (objectif > 70%)
- La duplication de code

Configuration dans `sonar-project.properties` :
```properties
sonar.projectKey=foodmarket-php
sonar.sources=app/
sonar.tests=tests/
sonar.php.coverage.reportPaths=build/coverage/clover.xml
```

### 9.2 Audit des dépendances

`composer audit` vérifie les CVE connues dans les packages Composer.
Si une vulnérabilité critique est détectée, la pipeline échoue et bloque le déploiement.

---

## 10. Difficultés rencontrées et solutions

### 10.1 Tests sans MySQL en CI

**Problème** : Les tests PHPUnit nécessitaient MySQL, indisponible dans GitHub Actions sans service dédié.

**Solution** : Configuration d'un groupe de base de données `tests` dans `app/Config/Database.php` utilisant SQLite en mémoire. Les tests s'exécutent sans dépendance externe.

```php
'tests' => [
    'DBDriver' => 'SQLite3',
    'database' => ':memory:',
]
```

### 10.2 Permissions Docker

**Problème** : Le dossier `writable/` causait des erreurs de permission dans le container.

**Solution** : Ajout explicite dans le Dockerfile :
```dockerfile
RUN chown -R www-data:www-data writable && chmod -R 755 writable
```

### 10.3 Cache Docker en CI

**Problème** : Chaque build CI reconstruisait l'image depuis zéro (lent).

**Solution** : Utilisation du cache GitHub Actions avec `docker/build-push-action` :
```yaml
cache-from: type=gha
cache-to: type=gha,mode=max
```

### 10.5 Conflit de versions Composer

**Problème** : `codeigniter4/devkit` était incompatible avec `phpunit ^10.5` — devkit exigeait phpunit ^9.3 et tirait des packages lourds (rector, psalm, phpstan) inutiles pour les tests.

**Solution** : Suppression de `devkit` et déclaration directe des dépendances de test nécessaires dans `composer.json` :
```json
"require-dev": {
    "phpunit/phpunit": "^10.5",
    "fakerphp/faker": "^1.23",
    "mikey179/vfsstream": "^1.6",
    "nexusphp/tachycardia": "^2.0"
}
```
Cette approche réduit le nombre de packages de 105 à 34 et élimine les conflits de versions.

**Problème** : CI4 écrit les logs en texte brut par défaut, incompatible avec Logstash.

**Solution** : Création d'un handler personnalisé dans `app/Config/Logger.php` qui formate chaque entrée en JSON structuré avant l'écriture.

---

## 11. Conclusion

Ce projet démontre une chaîne DevOps complète et fonctionnelle :

- **Application** : API REST PHP CodeIgniter 4 avec CRUD produits, tests unitaires et couverture de code
- **CI/CD** : Pipeline GitHub Actions automatisant lint, tests, build Docker, analyse SonarQube et déploiement
- **Monitoring** : Prometheus + Grafana avec alertes sur les métriques critiques
- **Logs** : Stack ELK centralisant les logs JSON de l'application
- **Kubernetes** : Déploiement scalable avec auto-scaling et zéro downtime

L'ensemble du projet est disponible sur GitHub et l'image Docker est publiée sur Docker Hub sous `derrickdev/foodmarket-php`.
