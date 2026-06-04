# Dossier Projet — Titre Professionnel DWWM
## Application prescrip_pin40 — Gestion de prescriptions numériques

> Référentiel : REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion  
> Titre Professionnel : Développeur Web et Web Mobile (TP-01280m04)

---

## Présentation du projet

**prescrip_pin40** est une application web de gestion de prescriptions numériques dans le cadre de l'inclusion numérique. Elle permet à des structures prescriptrices (associations, services sociaux, mairies) d'orienter des bénéficiaires vers des lieux de médiation numérique pour l'acquisition de compétences numériques de base.

### Contexte métier

Le projet s'inscrit dans le dispositif national **Aidants Connect / Pass Numérique** pour la lutte contre l'illectronisme. Le flux métier implique trois acteurs :

| Acteur | Rôle | Périmètre applicatif |
|--------|------|---------------------|
| **Prescripteur** | Structure sociale qui identifie le besoin | Créer/valider la prescription côté bénéficiaire |
| **Médiateur** | Lieu de médiation numérique | Compléter et clôturer la prescription |
| **Administrateur** | Pilotage global du dispositif | Supervision, reporting, gestion des structures |

### Stack technique

```
┌─────────────────────────────────────────────────────────────┐
│                      APPLICATION WEB                        │
│                                                             │
│   FrankenPHP 1.10 + PHP 8.5          Symfony 7.3           │
│   ┌─────────────────────────────────────────────────────┐   │
│   │  Frontend                    Backend                │   │
│   │  Bootstrap 5    ←──→   Controllers Symfony          │   │
│   │  Stimulus.js             Doctrine ORM               │   │
│   │  Turbo/Hotwire            Services métier           │   │
│   │  Webpack Encore           Twig Templates            │   │
│   └─────────────────────────────────────────────────────┘   │
│                                                             │
│   Services Docker                                           │
│   ┌────────────┐  ┌───────────────┐  ┌──────────────────┐  │
│   │MariaDB/MySQL│  │Elasticsearch  │  │Gotenberg (PDF)   │  │
│   │             │  │7.17           │  │                  │  │
│   └────────────┘  └───────────────┘  └──────────────────┘  │
│                                                             │
│   Service externe : Docuseal (signature électronique)       │
└─────────────────────────────────────────────────────────────┘
```

---

## Structure du dossier de certification

Ce dossier est organisé en **2 CCP** couvrant **8 Compétences Professionnelles** :

---

## CCP 1 — Développer la partie front-end d'une application web ou web mobile sécurisée

| # | Compétence Professionnelle | Fichier |
|---|---------------------------|---------|
| CP1 | Installer et configurer son environnement de travail en fonction du projet web ou web mobile | [cp1-environnement-travail.md](./cp1-environnement-travail.md) |
| CP2 | Maquetter des interfaces utilisateur web ou web mobile | [cp2-maquettage-interfaces.md](./cp2-maquettage-interfaces.md) |
| CP3 | Réaliser des interfaces utilisateur statiques web ou web mobile | [cp3-interfaces-statiques.md](./cp3-interfaces-statiques.md) |
| CP4 | Développer la partie dynamique des interfaces utilisateur web ou web mobile | [cp4-interfaces-dynamiques.md](./cp4-interfaces-dynamiques.md) |

---

## CCP 2 — Développer la partie back-end d'une application web ou web mobile sécurisée

| # | Compétence Professionnelle | Fichier |
|---|---------------------------|---------|
| CP5 | Mettre en place une base de données relationnelle | [cp5-base-de-donnees.md](./cp5-base-de-donnees.md) |
| CP6 | Développer des composants d'accès aux données SQL et NoSQL | [cp6-acces-donnees-sql-nosql.md](./cp6-acces-donnees-sql-nosql.md) |
| CP7 | Développer des composants métier côté serveur | [cp7-composants-metier-serveur.md](./cp7-composants-metier-serveur.md) |
| CP8 | Documenter le déploiement d'une application dynamique web ou web mobile | [cp8-documentation-deploiement.md](./cp8-documentation-deploiement.md) |

---

## Technologies couvertes par ce projet

### Front-end
- **HTML5 / CSS3** — Templates Twig, structure sémantique
- **Bootstrap 5** — Framework CSS responsive, composants UI
- **SCSS** — Préprocesseur CSS, variables, nesting (via Webpack Encore)
- **JavaScript ES6+** — Stimulus.js (controllers), Turbo (navigation SPA-like)
- **Webpack Encore** — Bundler assets (JS/CSS)
- **Accessibilité** — Attributs ARIA, labels de formulaires, contrastes

### Back-end
- **PHP 8.5** — Langage serveur, Enums natifs, attributs, types stricts
- **Symfony 7.3** — Framework MVC, DI Container, Event System
- **Doctrine ORM 3** — Mapping objet-relationnel, migrations, QueryBuilder
- **MariaDB/MySQL** — Base de données relationnelle
- **Elasticsearch 7.17** — Moteur de recherche full-text (NoSQL)
- **FrankenPHP 1.10** — Serveur HTTP intégré (basé sur Caddy + PHP FPM)

### Services & Intégrations
- **Gotenberg 8** — Génération de PDF depuis HTML (via API REST)
- **Docuseal** — Signature électronique de documents
- **chillerlan/php-qrcode** — Génération de QR codes
- **symfonycasts/verify-email-bundle** — Vérification email à l'inscription
- **FOSElasticaBundle** — Intégration Elasticsearch dans Symfony

### Infrastructure & DevOps
- **Docker / Docker Compose** — Conteneurisation multi-services
- **FrankenPHP** — Remplacement de PHP-FPM + Nginx/Apache
- **Git** — Versioning du code source
- **PHPUnit 12** — Tests unitaires et fonctionnels

---

## Architecture applicative

### Structure du code source

```
src/
├── Config/              # Enums PHP : StatusPrescription, StepPrescription, Civility
├── Controller/
│   ├── Admin/           # Espace administration (Dashboard, Members, Structures)
│   ├── Gestapp/         # Gestion des prescriptions (Prescription, Beneficiary, Equipment…)
│   └── Webapp/          # Pages publiques (accueil, contact, mentions légales)
├── Entity/
│   ├── Admin/           # Entités administratives : Member, Structure
│   ├── Gestapp/         # Entités métier : Prescription, Beneficiary, Equipment…
│   └── Serv/            # Entités de services : Docuseal
├── EventListener/       # Listeners Symfony (session timeout, indexation Elasticsearch)
├── Form/                # Types de formulaires Symfony
├── Repository/          # Repositories Doctrine (accès données)
├── Security/            # EmailVerifier
└── Service/             # Services applicatifs (QrcodeGenerator)

templates/
├── admin/               # Templates espace admin
├── composants/          # Bibliothèque de composants réutilisables
├── gestapp/             # Templates espace métier
├── include/             # Éléments partagés (navbar, footer, modals, toaster)
└── webapp/              # Pages publiques
```

### Workflow prescription (machine à états)

```
[CRÉATION]
     │
     ▼
 ┌─────────┐    ┌──────────┐    ┌──────────────┐
 │  Open   │───▶│ OneParts │───▶│   TwoParts   │
 │ (Admin) │    │(Prescr.) │    │ (Prescr.+Med)│
 └─────────┘    └──────────┘    └──────────────┘
                                       │
                                       ▼
                               ┌───────────────┐
                               │ChoiceEquipment│
                               │  (Médiateur)  │
                               └───────────────┘
                                       │
                                       ▼
                               ┌───────────────┐    ┌──────────┐
                               │  ValidCase    │───▶│  Signed  │
                               │  (Validation) │    │  (Final) │
                               └───────────────┘    └──────────┘
```

---

## Critères de sécurité transversaux

- **Authentification** : Symfony Security Bundle, hachage BCrypt des mots de passe
- **Autorisation** : Contrôle d'accès par rôles (RBAC), `access_control` dans `security.yaml`
- **Protection CSRF** : Tokens CSRF sur tous les formulaires, validation côté serveur ET client
- **Validation** : Symfony Validator sur toutes les entités
- **Sessions** : Timeout de session automatique via `SessionTimeoutListener`
- **Variables sensibles** : Gestion via `.env.local` (non versionné), secrets Symfony
- **HTTPS** : FrankenPHP avec TLS automatique (Caddy)

---

*Document généré dans le cadre de la certification Titre Professionnel DWWM*  
*Date : Juin 2026*
