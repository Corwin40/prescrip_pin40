# CP1 — Installer et configurer son environnement de travail en fonction du projet web ou web mobile

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP1 du titre professionnel DWWM couvre la capacité à mettre en place un environnement de développement complet, cohérent et reproductible. Elle implique :

- La sélection et l'installation des outils de développement adaptés au projet
- La configuration d'un environnement conteneurisé (Docker)
- La gestion des dépendances front-end (npm/Yarn) et back-end (Composer)
- La configuration des outils de build (Webpack Encore)
- La séparation des environnements développement / production
- La gestion des variables d'environnement et secrets

---

## 2. Contexte du projet

**prescrip_pin40** est une application web de gestion de prescriptions numériques dans le cadre du dispositif d'inclusion numérique dans les Landes (40). L'application met en relation des structures prescriptrices, des médiateurs numériques et des administrateurs du dispositif.

La stack technique retenue répond à des exigences de modernité, de performance et de maintenabilité :

| Couche | Technologie | Version |
|--------|-------------|---------|
| Serveur HTTP | FrankenPHP (basé sur Caddy) | 1.10 |
| Langage serveur | PHP | 8.5 |
| Framework PHP | Symfony | 7.3 |
| ORM | Doctrine | 3.x |
| Base de données | MariaDB | 10.11 |
| Moteur de recherche | Elasticsearch | 7.17.16 |
| Génération PDF | Gotenberg | 8 |
| Signature électronique | Docuseal | (service externe) |
| Bundler front-end | Webpack Encore | 5.x |
| Framework CSS | Bootstrap | 5.3 |
| JS interactif | Stimulus.js + Turbo | 3.x / 8.x |
| Gestionnaire paquets JS | Yarn | 4.x |
| Conteneurisation | Docker / Docker Compose | |

---

## 3. Conteneurisation avec Docker

### 3.1 Image FrankenPHP (Dockerfile)

Le projet utilise **FrankenPHP**, un serveur d'application PHP moderne qui intègre PHP-FPM et le serveur Caddy dans une seule image, éliminant le besoin d'un couple Nginx+PHP-FPM traditionnel.

```dockerfile
FROM dunglas/frankenphp:1.10-php8.5-alpine

# Extensions PHP nécessaires au projet
RUN install-php-extensions intl opcache gd zip pdo_mysql

# Outils Node.js pour le build des assets front-end
RUN apk add --no-cache nodejs npm nano git

# Yarn comme gestionnaire de paquets JS
RUN npm install -g yarn

# Composer (gestionnaire de dépendances PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
```

**Choix techniques expliqués :**

- `alpine` : image de base légère (~5 Mo vs ~100 Mo pour debian), réduit la surface d'attaque et le temps de pull
- `install-php-extensions` : outil officiel FrankenPHP pour compiler les extensions PHP sans friction
  - `intl` : internationalisation (formatage dates, monnaie)
  - `opcache` : cache bytecode PHP pour les performances en production
  - `gd` : manipulation d'images (QR codes)
  - `zip` : compression/décompression de fichiers
  - `pdo_mysql` : connexion à MariaDB/MySQL
- `nodejs npm yarn` : nécessaires pour compiler les assets avec Webpack Encore directement dans le container
- **Multi-stage** avec `composer:latest` : évite d'installer Composer via un script shell, garantit la dernière version stable

### 3.2 Orchestration Docker Compose

Le projet utilise **trois fichiers Compose** pour une séparation claire des environnements :

#### `compose.yaml` — Services communs (infra partagée)

```yaml
services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:7.17.16
    container_name: pin40prescription_search
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
    container_name: pin40prescription_gotenberg
    restart: always
    networks:
      - prescriptpin40

volumes:
  esdata:

networks:
  prescriptpin40:
    external: true
```

#### `compose.prod.yaml` — Environnement de production complet

```yaml
services:
  php:
    build: .
    container_name: pin40prescription_php
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./:/app
    environment:
      - SERVER_NAME=:80
    depends_on:
      - db
    networks:
      - prescriptpin40

  db:
    image: mariadb:10.11.2
    container_name: pin40prescription_db
    restart: unless-stopped
    environment:
      - MYSQL_DATABASE=prescrippin40_db
      - MYSQL_USER=xavier
      - MYSQL_PASSWORD=${DB_PASSWORD}
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
    ports:
      - "3307:3306"
    volumes:
      - db_data:/var/lib/mysql
    networks:
      - prescriptpin40

  phpmyadmin:
    image: phpmyadmin/phpmyadmin:latest
    container_name: pin40prescription_pma
    ports:
      - "8080:80"
    environment:
      - PMA_HOST=db
    networks:
      - prescriptpin40

  elasticsearch:
    # hérité de compose.yaml
    ...

  gotenberg:
    # hérité de compose.yaml
    ...
```

#### `compose.override.yaml` — Surcharge développement

```yaml
services:
  gotenberg:
    ports:
      - "3000:3000"  # expose Gotenberg en dev pour tests directs
```

**Tableau comparatif des environnements :**

| Élément | Développement | Production |
|---------|--------------|------------|
| Fichier principal | `compose.yaml` + `compose.override.yaml` | `compose.yaml` + `compose.prod.yaml` |
| Port HTTP | auto (FrankenPHP dev server) | 80 |
| Gotenberg port exposé | 3000 | non exposé |
| phpMyAdmin | non (accès direct VS Code) | oui (port 8080) |
| MariaDB | localhost DB partagée | container dédié (port 3307) |
| Variables sensibles | `.env.local` (non versionné) | secrets système / `.env.local` serveur |

---

## 4. Gestion des variables d'environnement

Symfony utilise une hiérarchie de fichiers `.env` pour la configuration :

```
.env              # valeurs par défaut (versionné, pas de secrets)
.env.dev          # surcharges dev (versionné si non sensible)
.env.test         # config pour les tests PHPUnit
.env.local        # secrets locaux (NON versionné, dans .gitignore)
```

Extrait de `.env` :
```env
APP_ENV=dev
APP_SECRET=votre_secret_ici

DATABASE_URL="mysql://user:password@127.0.0.1:3306/prescrippin40?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

ELASTICSEARCH_URL=http://localhost:9201

MAILER_DSN=null://null

GOTENBERG_DSN=http://gotenberg:3000
```

Les variables sensibles (mots de passe BDD, clés API Docuseal) sont placées dans `.env.local` qui n'est jamais versionné.

---

## 5. Gestion des dépendances

### 5.1 Dépendances PHP — Composer

```json
{
  "require": {
    "php": ">=8.2",
    "symfony/framework-bundle": "7.3.*",
    "symfony/security-bundle": "7.3.*",
    "doctrine/orm": "^3.5",
    "doctrine/doctrine-migrations-bundle": "^3.4",
    "friendsofsymfony/elastica-bundle": "^7.0",
    "sensiolabs/gotenberg-bundle": "^1.2",
    "docusealco/docuseal-php": "^1.0",
    "chillerlan/php-qrcode": "^6.0",
    "symfony/ux-turbo": "^2.30",
    "symfony/stimulus-bundle": "^2.30",
    "twbs/bootstrap": "^5.3"
  },
  "require-dev": {
    "phpunit/phpunit": "^12.3",
    "symfony/maker-bundle": "^1.0",
    "symfony/web-profiler-bundle": "7.3.*"
  }
}
```

Scripts automatisés post-installation :
```json
"scripts": {
    "auto-scripts": {
        "cache:clear": "symfony-cmd",
        "assets:install %PUBLIC_DIR%": "symfony-cmd"
    }
}
```

### 5.2 Dépendances JavaScript — package.json / Yarn

```json
{
  "devDependencies": {
    "@symfony/webpack-encore": "^5.0",
    "@hotwired/stimulus": "^3.0",
    "@hotwired/turbo": "^8.0",
    "@symfony/stimulus-bridge": "^3.2",
    "@symfony/ux-turbo": "file:vendor/symfony/ux-turbo/assets"
  },
  "dependencies": {
    "bootstrap": "^5.3",
    "sass": "^1.70",
    "sass-loader": "^14.0",
    "axios": "^1.12"
  }
}
```

---

## 6. Pipeline de build — Webpack Encore

```javascript
// webpack.config.js
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    // Points d'entrée : un bundle JS par contexte
    .addEntry('app', './assets/app.js')   // bundle principal
    .addEntry('pdf', './assets/pdf.js')   // bundle pour les vues PDF

    // Optimisation : découpe les chunks communs
    .splitEntryChunks()

    // Intégration Stimulus (chargement auto des controllers)
    .enableStimulusBridge('./assets/controllers.json')

    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()

    // Source maps en dev uniquement
    .enableSourceMaps(!Encore.isProduction())

    // Versioning des assets en prod (cache-busting)
    .enableVersioning(Encore.isProduction())

    // Support Babel avec polyfills automatiques
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })

    // Support SCSS
    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
```

**Commandes de build :**

```bash
# Développement (watch mode)
yarn encore dev --watch

# Production (minification + versioning)
yarn encore production
```

---

## 7. Configuration de l'IDE — PHPStorm

Le projet inclut une configuration PHPStorm (dossier `.idea/`) avec :

- Mapping de déploiement FTP/SFTP vers le serveur de production
- Configuration PHP interpreter (Docker container)
- Intégration Symfony (routes, services, templates)
- Configuration ESLint pour la qualité du code JavaScript
- Mapping des chemins Docker pour le debugging

Fichiers de configuration IDE :
```
.idea/
├── php.xml              # Interpréteur PHP configuré sur le container Docker
├── symfony2.xml         # Plugin Symfony activé
├── deployment.xml       # Déploiement FTP vers prod
├── sshConfigs.xml       # Connexions SSH
└── webServers.xml       # Serveurs web enregistrés
```

---

## 8. Procédure d'installation complète

```bash
# 1. Cloner le dépôt
git clone <url-repo> prescrip_pin40
cd prescrip_pin40

# 2. Créer le réseau Docker externe
docker network create prescriptpin40

# 3. Construire et démarrer les containers
docker compose -f compose.yaml -f compose.prod.yaml up -d --build

# 4. Installer les dépendances PHP
docker exec pin40prescription_php composer install

# 5. Configurer les variables d'environnement
cp .env .env.local
# Éditer .env.local avec les valeurs réelles

# 6. Créer la base de données et exécuter les migrations
docker exec pin40prescription_php php bin/console doctrine:database:create
docker exec pin40prescription_php php bin/console doctrine:migrations:migrate --no-interaction

# 7. Installer les dépendances JS et compiler les assets
docker exec pin40prescription_php yarn install
docker exec pin40prescription_php yarn encore production

# 8. Peupler l'index Elasticsearch
docker exec pin40prescription_php php bin/console fos:elastica:populate

# 9. Vider le cache
docker exec pin40prescription_php php bin/console cache:clear
```

---

## 9. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| L'environnement de travail est installé et fonctionnel | Docker Compose avec 5 services (PHP, MariaDB, Elasticsearch, Gotenberg, phpMyAdmin) |
| Les outils de développement sont configurés selon les besoins du projet | PHPStorm avec interpréteur Docker, Webpack Encore configuré avec 2 entry points |
| La veille sur les mises à jour des outils est effectuée | Versions épinglées dans Dockerfile et composer.json/package.json |
| L'environnement de dev est distinct de la production | 3 fichiers Compose séparés (compose.yaml / compose.override.yaml / compose.prod.yaml) |
| Les variables d'environnement sont gérées selon les bonnes pratiques | Hiérarchie .env Symfony, .env.local non versionné, séparation dev/test/prod |
| La gestion des dépendances est maîtrisée | Composer (PHP) + Yarn (JS), verrouillage des versions via composer.lock et yarn.lock |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*