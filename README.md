# Prescrip_Pin40
Application Symfony permettant la gestion des prescriptions, bénéficiaires, équipements et membres au sein d'une structure d'accompagnement.

##  Technologies utilisées
- **Symfony 6+**
- **PHP 8.2+**
- **MySQL 8**
- **Twig**
- **Doctrine ORM**
- **KnpPaginatorBundle**
- **Bootstrap 5**
- **Composer**
- **wkhtmltopdf** (génération PDF)

##  Installation du projet

### 1. Cloner le dépôt
```
git clone https://github.com/tonrepo/prescrip_pin40.git
cd prescrip_pin40
```

### 2. Installer les dépendances
```
composer install
```

### 3. Configurer les variables d’environnement
Créer le fichier `.env.local` :
```
APP_ENV=dev
APP_SECRET=une_clé_secrète
DATABASE_URL="mysql://root:@127.0.0.1:3306/prescrippin40_bdd?serverVersion=8.0.32&charset=utf8mb4"
```

### 4. Mettre à jour la base de données
```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Lancer le serveur Symfony
```
symfony server:start
```

##  Fonctionnalités principales
- Gestion des membres, bénéficiaires, équipements, prescriptions
- Pagination + recherche
- Génération PDF
- Dashboard administrateur
- Sécurité basée sur les rôles

## ⚠ Gestion des erreurs
Pages personnalisées dans :
```
templates/bundles/TwigBundle/Exception/
```

##  Licence
Projet interne.
