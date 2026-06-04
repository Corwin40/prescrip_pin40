# CP8 — Documenter le déploiement d'une application dynamique web ou web mobile

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP8 du titre professionnel DWWM couvre la capacité à documenter complètement le déploiement d'une application web dynamique. Elle implique :

- La rédaction d'une documentation technique de déploiement
- La description de l'architecture des services
- La documentation des prérequis et de la procédure d'installation
- La description de la gestion des environnements (dev/prod)
- La documentation des variables de configuration

---

## 2. Vue d'ensemble de l'architecture

### 2.1 Diagramme des services

```
┌─────────────────────────────────────────────────────────────────┐
│                        SERVEUR DE PRODUCTION                     │
│                                                                   │
│  ┌───────────────────────────────────────────────────────────┐   │
│  │           RÉSEAU DOCKER : prescriptpin40                  │   │
│  │                                                           │   │
│  │  ┌──────────────────┐    ┌─────────────────────────────┐ │   │
│  │  │  FrankenPHP 1.10  │    │   Elasticsearch 7.17.16      │ │   │
│  │  │  PHP 8.5-alpine   │    │   (pin40prescription_search) │ │   │
│  │  │  Symfony 7.3      │◄──►│   Port interne: 9200         │ │   │
│  │  │  Port: 80         │    │   Port hôte: 9201            │ │   │
│  │  │  (pin40presc_php) │    │   Volume: esdata             │ │   │
│  │  └────────┬──────────┘    └─────────────────────────────┘ │   │
│  │           │                                                 │   │
│  │           │               ┌─────────────────────────────┐ │   │
│  │           │               │   Gotenberg 8                │ │   │
│  │  ┌────────▼──────────┐    │   (pin40presc_gotenberg)     │ │   │
│  │  │  MariaDB 10.11    │    │   Port interne: 3000         │ │   │
│  │  │  (pin40presc_db)  │    └─────────────────────────────┘ │   │
│  │  │  Port hôte: 3307  │                                     │   │
│  │  │  Volume: db_data  │    ┌─────────────────────────────┐ │   │
│  │  └───────────────────┘    │   phpMyAdmin                 │ │   │
│  │                           │   (pin40presc_pma)           │ │   │
│  │                           │   Port hôte: 8080            │ │   │
│  │                           └─────────────────────────────┘ │   │
│  └───────────────────────────────────────────────────────────┘   │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │  SERVICE EXTERNE : Docuseal (dseal.openpixl.fr)             │ │
│  │  API de signature électronique - accès via HTTPS            │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Tableau des services

| Service | Image | Container | Port hôte | Port interne | Volume | Rôle |
|---------|-------|-----------|-----------|--------------|--------|------|
| **FrankenPHP** | `dunglas/frankenphp:1.10-php8.5-alpine` (build) | `pin40prescription_php` | 80 | 80 | `./:/app` | Serveur HTTP + PHP |
| **MariaDB** | `mariadb:10.11.2` | `pin40prescription_db` | 3307 | 3306 | `db_data` | Base de données |
| **Elasticsearch** | `elasticsearch:7.17.16` | `pin40prescription_search` | 9201 | 9200 | `esdata` | Moteur de recherche |
| **Gotenberg** | `gotenberg/gotenberg:8` | `pin40prescription_gotenberg` | — | 3000 | — | Génération PDF |
| **phpMyAdmin** | `phpmyadmin/phpmyadmin:latest` | `pin40prescription_pma` | 8080 | 80 | — | Interface BDD (admin) |
| **Docuseal** | externe | `dseal.openpixl.fr` | — | — | — | Signature électronique |

---

## 3. Prérequis

### 3.1 Serveur

| Prérequis | Version minimale | Vérification |
|-----------|-----------------|--------------|
| Docker | 24.x | `docker --version` |
| Docker Compose | 2.x (plugin V2) | `docker compose version` |
| Mémoire RAM | 4 Go minimum (8 Go recommandé) | Elasticsearch nécessite 2 Go alloués |
| Espace disque | 10 Go minimum | Pour les images Docker + volumes |
| OS | Linux (Ubuntu 22.04 LTS recommandé) | `uname -a` |

### 3.2 Réseau Docker

Le projet requiert un réseau Docker **externe** nommé `prescriptpin40` :

```bash
# Créer le réseau une seule fois sur le serveur
docker network create prescriptpin40

# Vérifier la création
docker network ls | grep prescriptpin40
```

Ce réseau externe est partagé entre plusieurs projets Docker sur le même serveur, permettant aux containers de communiquer entre eux.

---

## 4. Fichiers Docker Compose

### 4.1 `compose.yaml` — Services partagés (base commune)

```yaml
services:

  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:7.17.16
    container_name: pin40prescription_search
    volumes:
      - esdata:/usr/share/elasticsearch/data
    environment:
      - discovery.type=single-node          # Mode standalone (pas de cluster)
      - ES_JAVA_OPTS=-Xms2g -Xmx2g         # Alloue 2 Go RAM à Elasticsearch
    ports:
      - "9201:9200"                          # Port non standard pour éviter les conflits
      - "9301:9300"
    restart: always
    networks:
      - prescriptpin40

  gotenberg:
    image: 'gotenberg/gotenberg:8'
    container_name: pin40prescription_gotenberg
    restart: always
    networks:
      - prescriptpin40

volumes:
  esdata:                    # Volume persistant pour les données Elasticsearch

networks:
  prescriptpin40:
    external: true           # Réseau pré-existant, non géré par ce compose
```

### 4.2 `compose.override.yaml` — Surcharge développement

```yaml
services:
  gotenberg:
    ports:
      - "3000:3000"          # Expose Gotenberg en dev pour tests directs depuis le navigateur
```

### 4.3 `compose.prod.yaml` — Environnement de production complet

```yaml
services:

  php:
    build: .                             # Build depuis le Dockerfile local
    container_name: pin40prescription_php
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./:/app                          # Mount du code source dans le container
    environment:
      - SERVER_NAME=:80                  # FrankenPHP écoute sur le port 80
    depends_on:
      - db                               # Attend que MariaDB soit prêt
    networks:
      - prescriptpin40

  db:
    image: mariadb:10.11.2
    container_name: pin40prescription_db
    restart: unless-stopped
    environment:
      - MYSQL_DATABASE=prescrippin40_db
      - MYSQL_USER=xavier
      - MYSQL_PASSWORD=${DB_PASSWORD}    # Variable depuis .env.local
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
    ports:
      - "3307:3306"                      # Port non standard pour la sécurité
    volumes:
      - db_data:/var/lib/mysql           # Données persistantes
    networks:
      - prescriptpin40

  phpmyadmin:
    image: phpmyadmin/phpmyadmin:latest
    container_name: pin40prescription_pma
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      - PMA_HOST=db
      - PMA_PORT=3306
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
    depends_on:
      - db
    networks:
      - prescriptpin40

  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:7.17.16
    container_name: pin40_search
    volumes:
      - esdata:/usr/share/elasticsearch/data
    environment:
      - discovery.type=single-node
      - ES_JAVA_OPTS=-Xms2g -Xmx2g
    ports:
      - "9201:9200"
      - "9301:9300"
    restart: always
    networks:
      - prescriptpin40

  gotenberg:
    image: 'gotenberg/gotenberg:8'
    restart: always
    networks:
      - prescriptpin40

volumes:
  db_data:
  esdata:

networks:
  prescriptpin40:
    external: true
```

---

## 5. Dockerfile

```dockerfile
# Image de base : FrankenPHP 1.10 avec PHP 8.5 sur Alpine Linux
FROM dunglas/frankenphp:1.10-php8.5-alpine

# Extensions PHP nécessaires au projet
# - intl     : internationalisation (formatage, locales)
# - opcache  : cache bytecode PHP (performance en production)
# - gd       : manipulation d'images (QR codes)
# - zip      : compression/décompression
# - pdo_mysql: connexion MariaDB/MySQL
RUN install-php-extensions intl opcache gd zip pdo_mysql

# Outils système nécessaires au développement et au build
# - nodejs/npm : moteur JavaScript pour Webpack Encore
# - nano       : éditeur de texte pour debug sur le container
# - git        : nécessaire pour Composer (packages depuis GitHub)
RUN apk add --no-cache nodejs npm nano git

# Yarn comme gestionnaire de paquets JavaScript
RUN npm install -g yarn

# Composer (gestionnaire de dépendances PHP)
# Utilise le multi-stage build pour éviter d'alourdir l'image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Dossier de travail dans le container
WORKDIR /app
```

---

## 6. Variables d'environnement

### 6.1 Hiérarchie des fichiers .env (Symfony)

```
.env                 # Valeurs par défaut (versionné dans Git)
  └── .env.dev       # Surcharges pour l'environnement dev (versionné)
  └── .env.test      # Config pour les tests PHPUnit (versionné)
  └── .env.local     # Secrets et surcharges locales (NON VERSIONNÉ - dans .gitignore)
```

### 6.2 Variables requises

| Variable | Description | Exemple | Sensible |
|----------|-------------|---------|---------|
| `APP_ENV` | Environnement (dev/prod/test) | `prod` | Non |
| `APP_SECRET` | Clé secrète Symfony (CSRF, sessions) | `abc123...` | **OUI** |
| `DATABASE_URL` | DSN MariaDB complet | `mysql://user:pass@db:3306/prescrippin40` | **OUI** |
| `ELASTICSEARCH_URL` | URL du service Elasticsearch | `http://elasticsearch:9200` | Non |
| `MAILER_DSN` | DSN du serveur mail | `smtp://user:pass@smtp.host:587` | **OUI** |
| `GOTENBERG_DSN` | URL du service Gotenberg | `http://gotenberg:3000` | Non |
| `DOCUSEAL_API_KEY` | Clé API Docuseal | `sk_...` | **OUI** |

### 6.3 Fichier `.env` (extrait)

```env
APP_ENV=dev
APP_SECRET=changeme

# Format : mysql://USER:PASSWORD@HOST:PORT/DB?serverVersion=X&charset=utf8mb4
DATABASE_URL="mysql://xavier:password@db:3306/prescrippin40_db?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

ELASTICSEARCH_URL=http://elasticsearch:9200

MAILER_DSN=null://null

###> sensiolabs/gotenberg-bundle ###
GOTENBERG_DSN=http://gotenberg:3000
###< sensiolabs/gotenberg-bundle ###
```

---

## 7. Procédures de déploiement

### 7.1 Premier déploiement (installation complète)

```bash
# ─────────────────────────────────────────────────────────────
# ÉTAPE 1 : Préparation du serveur
# ─────────────────────────────────────────────────────────────

# Cloner le dépôt Git
git clone <url-du-depot> /var/www/prescrip_pin40
cd /var/www/prescrip_pin40

# Créer le réseau Docker externe (une seule fois par serveur)
docker network create prescriptpin40

# ─────────────────────────────────────────────────────────────
# ÉTAPE 2 : Configuration des variables d'environnement
# ─────────────────────────────────────────────────────────────

# Copier le fichier .env de base
cp .env .env.local

# Éditer .env.local avec les vraies valeurs
nano .env.local
# → Définir APP_SECRET, DATABASE_URL, DOCUSEAL_API_KEY, etc.

# ─────────────────────────────────────────────────────────────
# ÉTAPE 3 : Lancement des containers
# ─────────────────────────────────────────────────────────────

# Construction et démarrage de tous les services
docker compose -f compose.yaml -f compose.prod.yaml up -d --build

# Vérifier que tous les containers sont démarrés
docker compose -f compose.yaml -f compose.prod.yaml ps

# ─────────────────────────────────────────────────────────────
# ÉTAPE 4 : Installation des dépendances PHP
# ─────────────────────────────────────────────────────────────

docker exec pin40prescription_php composer install --no-dev --optimize-autoloader

# ─────────────────────────────────────────────────────────────
# ÉTAPE 5 : Initialisation de la base de données
# ─────────────────────────────────────────────────────────────

# Créer la base de données
docker exec pin40prescription_php php bin/console doctrine:database:create

# Exécuter toutes les migrations
docker exec pin40prescription_php php bin/console doctrine:migrations:migrate --no-interaction

# ─────────────────────────────────────────────────────────────
# ÉTAPE 6 : Build des assets front-end
# ─────────────────────────────────────────────────────────────

docker exec pin40prescription_php yarn install
docker exec pin40prescription_php yarn encore production

# ─────────────────────────────────────────────────────────────
# ÉTAPE 7 : Initialisation Elasticsearch
# ─────────────────────────────────────────────────────────────

# Créer les index Elasticsearch
docker exec pin40prescription_php php bin/console fos:elastica:create

# Peupler les index depuis la base de données SQL
docker exec pin40prescription_php php bin/console fos:elastica:populate

# ─────────────────────────────────────────────────────────────
# ÉTAPE 8 : Nettoyage du cache
# ─────────────────────────────────────────────────────────────

docker exec pin40prescription_php php bin/console cache:clear --env=prod
docker exec pin40prescription_php php bin/console assets:install

# ─────────────────────────────────────────────────────────────
# ÉTAPE 9 : Vérification
# ─────────────────────────────────────────────────────────────

# Vérifier que l'application répond
curl -I http://localhost:80

# Vérifier Elasticsearch
curl http://localhost:9201/_cluster/health?pretty

# Vérifier les logs FrankenPHP
docker logs pin40prescription_php --tail=50
```

### 7.2 Mise à jour (déploiement continu)

```bash
cd /var/www/prescrip_pin40

# 1. Récupérer les derniers commits
git pull origin master

# 2. Rebuilder l'image si le Dockerfile a changé
docker compose -f compose.yaml -f compose.prod.yaml up -d --build php

# 3. Mettre à jour les dépendances PHP si composer.json a changé
docker exec pin40prescription_php composer install --no-dev --optimize-autoloader

# 4. Exécuter les nouvelles migrations
docker exec pin40prescription_php php bin/console doctrine:migrations:migrate --no-interaction

# 5. Rebuilder les assets si les fichiers JS/SCSS ont changé
docker exec pin40prescription_php yarn encore production

# 6. Vider le cache
docker exec pin40prescription_php php bin/console cache:clear --env=prod

# 7. Re-peupler Elasticsearch si le mapping a changé
docker exec pin40prescription_php php bin/console fos:elastica:populate
```

### 7.3 Environnement de développement

```bash
# Lancer l'environnement de dev (compose.override.yaml activé automatiquement)
docker compose up -d

# Watch mode pour les assets (recompilation automatique)
docker exec -it pin40prescription_php yarn encore dev --watch

# Accéder au container PHP
docker exec -it pin40prescription_php sh

# Commandes Symfony fréquentes en dev
docker exec pin40prescription_php php bin/console debug:router          # Lister les routes
docker exec pin40prescription_php php bin/console debug:container        # Services DI
docker exec pin40prescription_php php bin/console doctrine:schema:validate # Valider le schéma
```

---

## 8. Commandes de maintenance

```bash
# ─── Symfony ───────────────────────────────────────────────────────────────
php bin/console cache:clear                    # Vider le cache
php bin/console cache:warmup                   # Préchauffer le cache
php bin/console assets:install                 # Publier les assets des bundles
php bin/console debug:router                   # Liste des routes
php bin/console doctrine:migrations:status     # État des migrations

# ─── Doctrine ──────────────────────────────────────────────────────────────
php bin/console doctrine:migrations:migrate    # Appliquer les migrations
php bin/console doctrine:migrations:diff       # Générer une migration depuis les entités
php bin/console doctrine:schema:validate       # Valider la cohérence ORM ↔ BDD

# ─── Elasticsearch ─────────────────────────────────────────────────────────
php bin/console fos:elastica:populate          # Ré-indexer toutes les entités
php bin/console fos:elastica:populate --index=prescription  # Index spécifique
php bin/console fos:elastica:reset             # Réinitialiser les index

# ─── Docker ────────────────────────────────────────────────────────────────
docker compose ps                              # État des containers
docker compose logs -f php                     # Logs en temps réel
docker compose restart php                     # Redémarrer le container PHP
docker system prune -af                        # Nettoyer les images/containers inutilisés
```

---

## 9. Résolution de problèmes courants

| Problème | Diagnostic | Solution |
|----------|-----------|----------|
| Page 500 | `docker logs pin40prescription_php` | Vérifier APP_SECRET et DATABASE_URL dans .env.local |
| Elasticsearch vide | `curl http://localhost:9201/_cat/indices` | `php bin/console fos:elastica:populate` |
| Assets 404 | `ls public/build/` | `yarn encore production` |
| Migration en erreur | `php bin/console doctrine:migrations:status` | Vérifier la BDD et les migrations précédentes |
| Container qui crash | `docker inspect pin40prescription_php` | Vérifier la mémoire disponible (Elasticsearch = 2 Go) |
| Permission denied sur `/public/` | `ls -la public/` | `chmod -R 777 public/prescriptions/` |

---

## 10. Sécurisation du déploiement

| Mesure | Implémentation |
|--------|---------------|
| Secrets non versionnés | `.env.local` dans `.gitignore` |
| HTTPS | FrankenPHP (Caddy) avec TLS automatique en production |
| Port BDD non standard | MariaDB sur 3307 (pas le 3306 standard) |
| phpMyAdmin restreint | Accessible uniquement sur le réseau interne (port 8080) |
| Mots de passe hachés | bcrypt via Symfony Security (`password_hashers: auto`) |
| Tokens CSRF | Activés sur tous les formulaires et le login |
| Timeout de session | 30 minutes via SessionTimeoutListener |

---

## 11. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| La documentation est complète et lisible | Procédures pas-à-pas numérotées, tableaux de référence, diagrammes ASCII |
| Les prérequis sont clairement identifiés | Docker, Docker Compose, réseau externe, RAM, OS |
| La procédure d'installation est reproductible | 9 étapes de déploiement initial + procédure de mise à jour |
| Les environnements dev et prod sont documentés | 3 fichiers Compose distincts, variables d'environnement séparées |
| La configuration des services est documentée | Tableau des 6 services avec ports, volumes, rôles |
| Les commandes de maintenance sont référencées | Section dédiée avec toutes les commandes Symfony/Doctrine/Elasticsearch/Docker |
| La résolution de problèmes est documentée | Tableau de 6 problèmes courants avec diagnostic et solution |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*