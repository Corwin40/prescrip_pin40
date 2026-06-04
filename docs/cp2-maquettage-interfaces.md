# CP2 — Maquetter des interfaces utilisateur web ou web mobile

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP2 du titre professionnel DWWM couvre la capacité à concevoir les interfaces utilisateur d'une application web avant de les développer. Elle implique :

- L'identification des utilisateurs cibles (personas)
- La définition des parcours utilisateurs (user journeys)
- La réalisation de maquettes et wireframes
- La conception d'une bibliothèque de composants réutilisables
- La prise en compte de l'accessibilité et du responsive design

---

## 2. Contexte du projet — Identification des utilisateurs

### 2.1 Personas (profils utilisateurs)

L'application **prescrip_pin40** répond aux besoins de 4 profils distincts :

| Persona | Rôle Symfony | Missions principales | Besoins UI |
|---------|-------------|---------------------|------------|
| **Travailleur social** | `ROLE_PRESCRIPTEUR` | Créer des prescriptions, suivre ses dossiers | Vue synthétique de ses prescriptions, formulaire simple |
| **Médiateur numérique** | `ROLE_MEDIATEUR` | Compléter les prescriptions, choisir équipements | Accès rapide aux dossiers à traiter, diagnostic compétences |
| **Responsable dispositif** | `ROLE_ADMIN` | Valider, superviser, gérer les structures | Dashboard global, liste admin, gestion utilisateurs |
| **Super administrateur** | `ROLE_SUPER_ADMIN` | Administration complète de la plateforme | Accès total, supervision multi-structures |

### 2.2 Arborescence de l'application

```
prescrip_pin40
│
├── / (PUBLIC)
│   ├── /                     Accueil (prezentation du dispositif)
│   ├── /contact              Formulaire de contact
│   └── /mentions-legales     Mentions légales
│
├── /admin (AUTHENTIFIÉ)
│   ├── /admin/login          Page de connexion
│   ├── /admin/dashboard      Tableau de bord (accueil connecté)
│   ├── /admin/member         Gestion des membres
│   │   ├── /new              Créer un membre
│   │   ├── /{id}/edit        Modifier un membre
│   │   └── /{id}             Fiche membre
│   ├── /admin/structure      Gestion des structures (admin+)
│   └── /admin/generatepdf    Génération PDF (admin+)
│
└── /gestapp (MÉTIER)
    ├── /gestapp/prescription  Liste / recherche des prescriptions
    │   ├── /new               Créer une prescription
    │   ├── /{id}              Détail d'une prescription
    │   ├── /{id}/edit         Éditer une prescription
    │   └── /admin             Vue admin des prescriptions signées
    ├── /gestapp/beneficiary   Gestion des bénéficiaires
    ├── /gestapp/equipment     Gestion des équipements
    ├── /gestapp/competence    Référentiel de compétences
    └── /gestapp/document      Documents attachés
```

---

## 3. Parcours utilisateurs (User Journeys)

### 3.1 Parcours prescripteur — Créer une prescription

```
[PRESCRIPTEUR]
      │
      ▼
 Connexion → /admin/login
      │
      ▼
 Dashboard → Vue de ses prescriptions en cours
      │
      ▼
 Nouvelle prescription → /gestapp/prescription/new
      │  Remplit : objet, bénéficiaire, lieu de médiation, motivation
      │
      ▼
 Prescription créée (step: OneParts)
      │  Le médiateur reçoit une notification
      ▼
 Attend la validation du médiateur
      │
      ▼
 Prescription en step: TwoParts → Génération PDF possible
```

### 3.2 Parcours médiateur — Compléter et valider

```
[MÉDIATEUR]
      │
      ▼
 Dashboard → Prescriptions à traiter
      │
      ▼
 Ouvre une prescription → /gestapp/prescription/{id}/edit
      │  Remplit : diagnostic compétences (DigComp), objectifs de médiation
      ▼
 Choisit l'équipement → step: ChoiceEquipment
      │
      ▼
 Valide le dossier → step: ValidCase
      │
      ▼
 Admin génère le PDF → step: GeneratePDF
      │
      ▼
 Admin envoie à Docuseal → step: SubmissionForSigned
      │
      ▼
 Signature électronique reçue → step: Signed
```

---

## 4. Wireframes des écrans principaux

### 4.1 Layout général (base.html.twig)

```
┌──────────────────────────────────────────────────────────┐
│ NAVBAR                                                    │
│ [Logo PIN40] Prescription Ordi PIN40    [User ▼] [Tutos] │
├──────────────────────────────────────────────────────────┤
│                                                           │
│  MAIN  (container Bootstrap)                              │
│                                                           │
│  ┌────────────────────────────────────────────────────┐  │
│  │                                                    │  │
│  │            {% block body %}                        │  │
│  │                                                    │  │
│  └────────────────────────────────────────────────────┘  │
│                                                           │
│  [MODALS — inclus ici mais cachés par défaut]             │
│  [TOASTERS — notifications Bootstrap]                     │
│                                                           │
├──────────────────────────────────────────────────────────┤
│ FOOTER                                                    │
└──────────────────────────────────────────────────────────┘
```

### 4.2 Dashboard principal

```
┌──────────────────────────────────────────────────────────┐
│ NAVBAR                                                    │
├──────────────────────────────────────────────────────────┤
│ [H1] Tableau de bord                                      │
│                                                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐    │
│  │ En cours     │  │ À valider    │  │ Signées      │    │
│  │ [Nombre]     │  │ [Nombre]     │  │ [Nombre]     │    │
│  │ prescriptions│  │ prescriptions│  │ prescriptions│    │
│  └──────────────┘  └──────────────┘  └──────────────┘    │
│                                                           │
│  ┌─────────────────────────────────────────────────────┐  │
│  │ [Bouton] Nouvelle prescription                      │  │
│  │ [Tableau de bord des prescriptions récentes]        │  │
│  │ REF | Bénéficiaire | Status | Étape | Actions       │  │
│  │ ─── │ ─────────── │ ────── │ ───── │ ───────        │  │
│  │ ... │ ...         │ ...    │ ...   │ [Edit][Del]    │  │
│  └─────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
```

### 4.3 Formulaire de prescription (multi-sections)

```
┌──────────────────────────────────────────────────────────┐
│ Réf: 20260604-structX-42 | Ouvert par: prescripteur      │
│ [Badge status étape]                                      │
├──────────────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────────────┐  │
│ │ Objet de la prescription                            │  │
│ │ [Input text: objectName]                            │  │
│ └─────────────────────────────────────────────────────┘  │
│                                                           │
│ ┌─────────────────────────────────────────────────────┐  │
│ │ Bénéficiaire                                        │  │
│ │ Prénom [___] Nom [___] Civilité [◉M ○Mme]           │  │
│ │ Statut prof. [select▼]  Tranche d'âge [select▼]    │  │
│ └─────────────────────────────────────────────────────┘  │
│                                                           │
│ ┌─────────────────────────────────────────────────────┐  │
│ │ La médiation numérique                              │  │
│ │ Lieu de médiation [select▼]                         │  │
│ └─────────────────────────────────────────────────────┘  │
│                                                           │
│ ┌─────────────────────────────────────────────────────┐  │
│ │ Diagnostic des compétences numériques               │  │
│ │ [Tableau] Compétences | Acquis | En cours | Non acq │  │
│ │ Les bases         [○]   [○]       [●]               │  │
│ │ Texte/documents   [○]   [●]       [○]               │  │
│ │ Internet          [●]   [○]       [○]               │  │
│ │ Messagerie        [○]   [○]       [●]               │  │
│ └─────────────────────────────────────────────────────┘  │
│                                                           │
│ ┌─────────────────────────────────────────────────────┐  │
│ │ Les objectifs de la médiation (DigComp)             │  │
│ │ ☐ 0 - Compétences matérielles                       │  │
│ │ ☑ 1 - Information et données                        │  │
│ │ ☐ 2 - Communication et collaboration                │  │
│ └─────────────────────────────────────────────────────┘  │
│                                                           │
│ [Retour ←]          [Préenregistrer la demande ✓]         │
└──────────────────────────────────────────────────────────┘
```

### 4.4 Navbar responsive

```
Desktop (lg+):
┌──────────────────────────────────────────────────────────┐
│ [Logo] Prescription Ordi PIN40  | [Tutos] [👤 Prénom ▼] │
└──────────────────────────────────────────────────────────┘

Mobile (<lg) :
┌──────────────────────────────┐
│ [Logo] Prescription PIN40 [☰]│
└──────────────────────────────┘
  ↓ Toggle ouvert :
┌──────────────────────────────┐
│ Tutos                        │
│ Prénom Nom | Structure ▼     │
│   Mes informations           │
│   Fiches prescriptions       │
│   Se déconnecter             │
└──────────────────────────────┘
```

---

## 5. Bibliothèque de composants réutilisables

Le dossier `templates/composants/` constitue une **bibliothèque de composants Twig** permettant une cohérence visuelle sur toute l'application.

### 5.1 Inventaire des composants

```
templates/composants/
├── forms/
│   ├── button_creatnew.html.twig       Bouton "Nouveau" standard
│   ├── button_link.html.twig           Lien stylisé comme bouton
│   ├── button_return_dashboard.html.twig Bouton retour tableau de bord
│   └── buttons_delete.html.twig        Bouton supprimer avec confirmation
├── inputs/
│   ├── input_text.html.twig            Champ texte Bootstrap stylisé
│   ├── input_textarea.html.twig        Zone de texte
│   ├── input_select.html.twig          Liste déroulante
│   ├── input_date.html.twig            Champ date
│   ├── input_checkbox.html.twig        Case à cocher simple
│   ├── input_checkbox_switch.html.twig Case à cocher toggle switch
│   ├── input_checkbox_tablerow.html.twig Checkbox dans tableau
│   ├── input_radio_horizontal.html.twig Boutons radio en ligne
│   ├── input_radio_tablerow.html.twig   Boutons radio dans tableau
│   └── collectiontype.html.twig        Collection Symfony Form
├── pages/
│   ├── pages_headers.html.twig         En-tête de page standardisé
│   └── page_checkboxs_inline.html.twig Cases à cocher inline
├── showsTable/
│   ├── button_return_list.html.twig    Retour vers la liste
│   ├── button_update.html.twig         Bouton modifier
│   └── buttons_delete.html.twig        Bouton supprimer (dans tableau)
└── fontawesome/
    ├── square-check.html.twig          Icône case cochée
    └── square.html.twig                Icône case vide
```

### 5.2 Interface de chaque composant (API Twig)

```twig
{# Utilisation d'un input_text #}
{% include 'composants/inputs/input_text.html.twig' with {
    form: form.objectName,      {# Le champ de formulaire Symfony #}
    disabled: false,             {# Désactiver le champ ou non #}
    show_label: true,            {# Afficher ou masquer le label #}
    wrapper_class: 'mb-3'        {# Classe CSS du wrapper (optionnel) #}
} %}

{# Utilisation d'un input_select #}
{% include 'composants/inputs/input_select.html.twig' with {
    form: form.lieuMediation,
    disabled: '',
    show_label: true
} %}

{# Utilisation d'un checkbox switch #}
{% include 'composants/inputs/input_checkbox_switch.html.twig' with {
    form: form.competence.isAutoEva,
    form_label: 'L\'auto évaluation a été réalisée.',
    disabled: false
} %}
```

---

## 6. Adaptation de l'interface selon le rôle

Une des particularités du projet est l'**adaptation dynamique de l'interface selon le rôle** de l'utilisateur connecté. Le même formulaire de prescription s'affiche différemment selon qu'on est prescripteur, médiateur ou administrateur.

```twig
{# Extrait de _form.html.twig — adaptation des champs selon le rôle #}

{# Bloc diagnostic compétences : lecture seule pour le prescripteur #}
{% if role == 'Prescripteur' %}
    {% include 'composants/inputs/input_radio_tablerow.html.twig' with {
        label: 'Les bases',
        form: form.competence.compBase,
        disabled: true,    {# Lecture seule : le prescripteur ne remplit pas ce bloc #}
        show_label: true
    } %}
{% elseif role == 'Mediateur' %}
    {% include 'composants/inputs/input_radio_tablerow.html.twig' with {
        label: 'Les bases',
        form: form.competence.compBase,
        disabled: false,   {# Éditable : le médiateur remplit ce bloc #}
        show_label: true
    } %}
{% endif %}

{# Message contextuel selon le rôle et le statut de la prescription #}
{% if role == 'Prescripteur' %}
    <p class="alert alert-warning">Ce bloc ne pourra être complété que par l'espace de médiation.</p>
{% elseif role == 'Médiateur' %}
    <p class="alert alert-info">Completez le parcours de formation adapté aux besoins du bénéficiaire.</p>
{% endif %}
```

---

## 7. Accessibilité et responsive design

### 7.1 Responsive Bootstrap 5

Le projet utilise le système de grille Bootstrap 5 (12 colonnes) pour l'adaptation aux différentes tailles d'écran :

```twig
{# Exemple dans _form.html.twig : grille responsive #}
<div class="row">
    <div class="col-md-6">      {# 6/12 sur tablette/desktop, 12/12 sur mobile #}
        {% include 'composants/inputs/input_select.html.twig' with {
            form: form.lieuMediation, ...
        } %}
    </div>
    <div class="col-md-6">
        {# autre champ #}
    </div>
</div>
```

La navbar utilise le composant responsive Bootstrap (`navbar-expand-lg`) qui bascule en menu hamburger sous 992px.

### 7.2 Accessibilité

```html
<!-- Bouton hamburger avec ARIA pour l'accessibilité -->
<button class="navbar-toggler" type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNavDropdown"
        aria-controls="navbarNavDropdown"
        aria-expanded="false"
        aria-label="Toggle navigation">   <!-- Label explicite pour les lecteurs d'écran -->
    <span class="navbar-toggler-icon"></span>
</button>

<!-- Logo avec alt text descriptif -->
<img src="{{ asset('media/png/logo_pin40.png') }}"
     alt="Logo dispositif PIN 40"
     height="36px">

<!-- Formulaires : labels explicites liés aux inputs via Symfony Form -->
<label class="form-label mb-1">{{ form_label(form) }}</label>
{{ form_widget(form, { attr: { class: 'form-control form-control-sm' } }) }}
```

---

## 8. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| Les personas sont identifiés | 4 profils utilisateurs avec rôles, missions et besoins UI distincts |
| Les parcours utilisateurs sont définis | Parcours prescripteur (création) et médiateur (complétion) documentés |
| Des wireframes sont réalisés | Wireframes textuels des 4 écrans principaux (layout, dashboard, formulaire, navbar) |
| Une bibliothèque de composants est constituée | 25 composants Twig réutilisables dans `templates/composants/` |
| L'accessibilité est prise en compte | Attributs ARIA sur navbar, alt text sur images, labels explicites |
| Le responsive design est intégré | Bootstrap 5 avec grille responsive, navbar hamburger mobile |
| L'interface s'adapte aux rôles | Champs en lecture/écriture, messages contextuels, boutons d'action différents |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*