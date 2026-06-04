# CP3 — Réaliser des interfaces utilisateur statiques web ou web mobile

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP3 du titre professionnel DWWM couvre la capacité à développer les interfaces statiques (structure HTML et styles CSS) d'une application web. Elle implique :

- La réalisation de pages HTML5 sémantiques
- L'intégration de styles CSS (ici via SCSS + Bootstrap 5)
- La mise en place d'un pipeline de build pour les assets (Webpack Encore)
- La création d'une bibliothèque de composants réutilisables
- Le respect des bonnes pratiques d'accessibilité et de responsive design

---

## 2. Stack front-end statique

| Technologie | Version | Rôle |
|-------------|---------|------|
| HTML5 / Twig | — | Templating (Symfony) |
| SCSS | — | Préprocesseur CSS |
| Bootstrap | 5.3 | Framework CSS responsive |
| Webpack Encore | 5.x | Pipeline de build des assets |
| PostCSS | — | Transformation CSS (autoprefixer) |
| FontAwesome Pro | — | Bibliothèque d'icônes |

---

## 3. Pipeline CSS — SCSS vers production

### 3.1 Configuration Webpack Encore

```javascript
// webpack.config.js
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')      // Assets compilés dans public/build/
    .setPublicPath('/build')             // URL publique des assets

    // Point d'entrée principal (importe app.scss)
    .addEntry('app', './assets/app.js')

    // Point d'entrée pour les PDF (styles PDF séparés)
    .addEntry('pdf', './assets/pdf.js')

    .splitEntryChunks()                  // Code splitting pour optimisation
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()

    // Source maps en dev uniquement (debug CSS dans les DevTools)
    .enableSourceMaps(!Encore.isProduction())

    // Versioning en prod : app.abc123.css (cache-busting)
    .enableVersioning(Encore.isProduction())

    // Support SCSS (sass-loader)
    .enableSassLoader()

    // Babel avec polyfills automatiques (compatibilité navigateurs)
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })
;
```

### 3.2 Point d'entrée SCSS principal

```scss
// assets/styles/app.scss

// Variable Bootstrap personnalisée (avant l'import Bootstrap)
$light: #f2f2f2;

// Import de Bootstrap (inclut reset CSS + toutes les classes utilitaires)
@import "~bootstrap/scss/bootstrap";

// Styles personnalisés du projet
@import "scss/custom";
```

**Avantages de cette approche :**
- La variable `$light` est définie AVANT l'import Bootstrap, ce qui surcharge la variable Bootstrap `$light` native
- Bootstrap est importé depuis `node_modules` via le tilde `~`
- Les styles personnalisés dans `scss/custom` bénéficient des variables Bootstrap

### 3.3 Commandes de build

```bash
# Développement : compilation une fois + watch automatique
yarn encore dev --watch

# Production : minification + versioning des fichiers
yarn encore production

# Production dans le container Docker
docker exec pin40prescription_php yarn encore production
```

---

## 4. Layout HTML5 — Template de base (base.html.twig)

```twig
{# templates/base.html.twig #}
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/jpg" href="{{ asset('media/png/logo_pin40.png') }}" />
        <title>{% block title %}Welcome!{% endblock %}</title>

        {% block stylesheets %}
            {# Injecte le tag <link> vers le CSS compilé par Webpack Encore #}
            {{ encore_entry_link_tags('app') }}
        {% endblock %}

        {% block javascripts %}
            {# Injecte les tags <script> (inclut le runtime Webpack + chunks) #}
            {{ encore_entry_script_tags('app') }}
            {# FontAwesome Pro via CDN #}
            <script src="https://kit.fontawesome.com/1fb9149f4c.js" crossorigin="anonymous"></script>
        {% endblock %}
    </head>

    {# data-page permet au JS de détecter la route active (dispatcher pattern) #}
    <body data-page="{{ app.current_route }}">
        <header class="mb-3">
            {% include 'include/navbar.html.twig' %}
        </header>

        {# Conteneur Bootstrap centré et responsive #}
        <main class="container">
            {% block body %}
            {% endblock %}

            {% block modals %}
                {% include 'include/modals.html.twig' %}
                {% include 'include/toaster.html.twig' %}
            {% endblock %}
        </main>

        <footer>
            {% include 'include/footer.html.twig' %}
        </footer>
    </body>
</html>
```

**Points techniques notables :**
- `data-page="{{ app.current_route }}"` : attribut data permettant au JavaScript de savoir quelle page est active (pattern dispatcher dans `assets/app.js`)
- `encore_entry_link_tags('app')` : fonction Twig injectant les bons tags `<link>` selon l'environnement (en dev : `app.css`, en prod : `app.abc123.css` versionné)
- Les modals et toasters sont inclus dans le layout pour être disponibles sur toutes les pages

---

## 5. Navigation — Navbar responsive

```twig
{# templates/include/navbar.html.twig #}
<nav class="navbar navbar-expand-lg shadow">
    <div class="container-fluid">

        {# Logo + nom de l'application #}
        <a class="navbar-brand" href="{{ path('app_admin_dashboard_index')}}">
            <img src="{{ asset('media/png/logo_pin40.png') }}"
                 alt="Logo dispositif PIN 40"
                 height="36px"
                 class="me-3">Prescription Ordi PIN40
        </a>

        {# Bouton hamburger pour mobile — ARIA inclus #}
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNavDropdown"
                aria-controls="navbarNavDropdown"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                {# Menu différent selon connexion #}
                {% if app.user %}
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fa-duotone fa-solid fa-lightbulb-exclamation-on"></i> Tutos
                        </a>
                    </li>

                    {# Dropdown utilisateur #}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-duotone fa-solid fa-circle-user"></i>&nbsp;
                            {{ app.user.firstname }} {{ app.user.lastname }}
                            | {{ app.user.structure.name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item"
                                   href="{{ path('app_admin_member_edit', {'id': app.user.id}) }}">
                                    <i class="fa-sharp-duotone fa-solid fa-circle-info"></i>
                                    Mes informations
                                </a>
                            </li>

                            {# Menu conditionnel selon le rôle #}
                            {% if is_granted('ROLE_ADMIN') %}
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ path('app_gestapp_prescription_foradmin') }}">
                                        <i class="fa-duotone fa-solid fa-file-certificate"></i>
                                        Fiches de prescriptions
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ path('app_admin_structure_index') }}">
                                        <i class="fa-duotone fa-solid fa-building-magnifying-glass"></i>
                                        Structures
                                    </a>
                                </li>
                            {% endif %}

                            {% if is_granted('ROLE_MEDIATEUR') %}
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ path('app_admin_member_index') }}">
                                        <i class="fa-duotone fa-solid fa-users"></i>
                                        Utilisateurs
                                    </a>
                                </li>
                            {% endif %}

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ path('app_logout') }}">
                                    <i class="fa-duotone fa-solid fa-arrow-left-from-bracket"></i>
                                    Se déconnecter
                                </a>
                            </li>
                        </ul>
                    </li>
                {% else %}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ path('app_admin_dashboard_index') }}">
                            <strong>Espace de connexion</strong>
                        </a>
                    </li>
                {% endif %}
            </ul>
        </div>
    </div>
</nav>
```

---

## 6. Bibliothèque de composants Twig

### 6.1 Composant input_text.html.twig

Ce composant illustre la philosophie de la bibliothèque : encapsuler les fonctions Twig de formulaire Symfony dans un wrapper Bootstrap cohérent.

```twig
{# templates/composants/inputs/input_text.html.twig #}
{% set disabled = disabled|default(false) %}
{% set wrapper_class = wrapper_class|default('mb-0') %}

<div class="{{ wrapper_class }}">

    {# Affichage conditionnel du label #}
    {% if show_label is true %}
        <label class="form-label mb-1">{{ form_label(form) }}</label>
    {% endif %}

    {# Widget du champ avec classes Bootstrap + état d'erreur #}
    {{ form_widget(form, {
        attr: {
            class: 'form-control form-control-sm border border-1 border-dark shadow-sm'
                ~ (form.vars.errors|length > 0 ? ' is-invalid' : '')
        } | merge(disabled ? {disabled:'disabled'} : {})
    }) }}

    {# Messages d'aide (hint) #}
    <span class="text-secondary">{{ form_help(form) }}</span>

    {# Messages d'erreur en rouge #}
    <span class="text-danger">{{ form_errors(form) }}</span>
</div>
```

**Paramètres du composant :**

| Paramètre | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `form` | FormView | requis | Le champ de formulaire Symfony |
| `disabled` | bool | false | Désactive le champ (lecture seule) |
| `show_label` | bool | false | Affiche ou masque le label |
| `wrapper_class` | string | 'mb-0' | Classe CSS du div wrapper |

### 6.2 Utilisation des composants dans les formulaires

```twig
{# Extrait de gestapp/prescription/_form.html.twig #}

{{ form_start(form) }}

    {# Champ texte simple #}
    {% include 'composants/inputs/input_text.html.twig' with {
        form: form.objectName,
        disabled: false,
        show_label: true
    } %}

    {# Select (liste déroulante) #}
    {% include 'composants/inputs/input_select.html.twig' with {
        form: form.lieuMediation,
        disabled: '',
        show_label: true
    } %}

    {# Textarea #}
    {% include 'composants/inputs/input_textarea.html.twig' with {
        form: form.details,
        rows: 5,
        disabled: false,
        show_label: true
    } %}

    {# Radio boutons horizontaux #}
    {% include 'composants/inputs/input_radio_horizontal.html.twig' with {
        label: 'Compétences de base',
        form: form.baseCompetence,
        show_label: true
    } %}

    {# Toggle switch #}
    {% include 'composants/inputs/input_checkbox_switch.html.twig' with {
        form: form.competence.isAutoEva,
        form_label: 'L\'auto évaluation a été réalisée.',
        disabled: false
    } %}

{{ form_end(form) }}
```

---

## 7. Grille responsive et classes Bootstrap

### 7.1 Système de grille utilisé

```twig
{# Exemple de mise en page responsive dans _form.html.twig #}
<div class="row">
    {# Sur mobile : col-12 (pleine largeur). Sur desktop : col-md-6 (moitié) #}
    <div class="col-md-6">
        {% include 'composants/inputs/input_select.html.twig' with { form: form.lieuMediation, ... } %}
    </div>
    <div class="col-md-6">
        {# Autre champ #}
    </div>
</div>

{# En-têtes de section avec fond et bordure #}
<div class="col-12 bg-light border-bottom border-dark mb-2">
    <h2 class="mt-1 mb-1 px-2 pt-1 h4">La médiation numérique</h2>
</div>
```

### 7.2 Classes Bootstrap utilisées

| Contexte | Classes Bootstrap | Description |
|----------|------------------|-------------|
| Grille | `row`, `col-12`, `col-md-6`, `col-4` | Mise en page responsive |
| Boutons | `btn btn-sm btn-outline-primary`, `btn-outline-danger` | Boutons stylisés |
| Alertes | `alert alert-info`, `alert alert-warning` | Messages contextuels |
| Tableaux | `table table-bordered table-striped table-hover` | Tableaux de données |
| Texte | `text-secondary`, `text-danger`, `fst-italic` | Styles typographiques |
| Navigation | `navbar`, `navbar-expand-lg`, `dropdown-menu-end` | Navigation responsive |
| Formulaires | `form-control`, `form-control-sm`, `form-label`, `is-invalid` | Champs de formulaire |
| Layout | `container`, `container-fluid`, `mb-3`, `mt-2`, `px-2` | Espacement |

---

## 8. Intégration des assets dans Symfony

Les assets compilés par Webpack Encore sont référencés dans les templates via les fonctions Twig fournies par `webpack-encore-bundle` :

```twig
{# Génère le tag <link> correct selon l'env (dev/prod) #}
{{ encore_entry_link_tags('app') }}
{# → dev  : <link rel="stylesheet" href="/build/app.css"> #}
{# → prod : <link rel="stylesheet" href="/build/app.abc123.css"> #}

{# Génère les tags <script> (runtime + chunks + entry) #}
{{ encore_entry_script_tags('app') }}

{# Référence une image ou ressource statique #}
<img src="{{ asset('media/png/logo_pin40.png') }}" alt="Logo">
```

---

## 9. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| La structure HTML5 est sémantique | `<header>`, `<main>`, `<nav>`, `<footer>` dans base.html.twig |
| Les styles sont maintenables | SCSS avec variables Bootstrap, fichier custom séparé |
| Le responsive design est implémenté | Bootstrap 5 grid, `navbar-expand-lg`, breakpoints `col-md-*` |
| L'accessibilité est respectée | ARIA sur navbar, alt text, labels liés aux inputs |
| Les composants sont réutilisables | 25 composants Twig paramétrables dans `templates/composants/` |
| Le pipeline de build est configuré | Webpack Encore avec SCSS, versioning, source maps |
| Les formulaires gèrent les erreurs | Classe `is-invalid` + affichage des erreurs Symfony (`form_errors`) |
| La cohérence visuelle est assurée | Même composant utilisé sur tous les formulaires |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*