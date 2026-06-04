# CP4 — Développer la partie dynamique des interfaces utilisateur web ou web mobile

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP4 du titre professionnel DWWM couvre la capacité à rendre les interfaces web interactives et dynamiques. Elle implique :

- Le développement de composants JavaScript réutilisables
- La communication asynchrone avec le serveur (AJAX / JSON)
- La mise à jour partielle du DOM sans rechargement complet de la page
- La protection CSRF côté client
- Le feedback utilisateur (notifications, indicateurs d'état)

---

## 2. Stack front-end dynamique

| Technologie | Version | Rôle |
|-------------|---------|------|
| Stimulus.js | `@hotwired/stimulus` ^3 | Controllers JS réutilisables, liés au DOM |
| Turbo | `@hotwired/turbo` ^8 | Navigation SPA-like, gestion soumission de formulaires |
| `@symfony/ux-turbo` | ^2.30 | Pont Symfony → Turbo (Turbo Streams) |
| `@symfony/stimulus-bridge` | ^3.2 | Autoload des controllers depuis `assets/controllers/` |
| Bootstrap 5 | ^5.3 | Composants UI : Modal, Toast |
| Webpack Encore | ^5 | Bundling JS/CSS |

---

## 3. Architecture JavaScript — Dispatcher par route

Le fichier `assets/app.js` implémente un pattern **dispatcher** : selon la route active (stockée dans `data-page` du `<body>`), il initialise les fonctions JavaScript propres à chaque page.

```javascript
// assets/app.js

import './bootstrap.js';        // Initialise Stimulus + Turbo
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;   // Expose Bootstrap globalement pour les modals

import './styles/app.scss';

// Import des modules JS par page
import { initIndex_Prescription } from "./js/pages/gestapp/prescription/index_prescription";
import { initNewEdit_Prescription } from "./js/pages/gestapp/prescription/newedit_prescription";
import { initIndex_Member } from "./js/pages/admin/member";
import { initIndex_Dashboard } from "./js/pages/admin/dashboard";
import { initAdmin_ListPrescription } from "./js/pages/gestapp/prescription/adminListPrescription";
import { initIndex_Beneficiary } from "./js/pages/gestapp/beneficiary/indexBeneficiary";

// Dispatcher : initialise le bon module selon la route active
document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;  // lit data-page="{{ app.current_route }}"

    switch (page) {
        case 'app_admin_dashboard_index':
            initIndex_Dashboard();
            break;
        case 'app_admin_member_index':
            initIndex_Member();
            break;
        case 'app_gestapp_prescription_index':
            initIndex_Prescription();
            break;
        case 'app_gestapp_prescription_new':
        case 'app_gestapp_prescription_edit':
            initNewEdit_Prescription();    // Même logique pour new et edit
            break;
        case 'app_gestapp_prescription_foradmin':
            initAdmin_ListPrescription();
            break;
        case 'app_gestapp_beneficiary_index':
            initIndex_Beneficiary();
            break;
    }
});
```

**Avantages de ce pattern :**
- Code JS chargé une seule fois (bundle unique)
- Logique isolée par page
- Pas de conflits entre pages différentes
- Facile à déboguer (savoir quelle `init*` est appelée)

---

## 4. Stimulus.js — Controllers réutilisables

### 4.1 Initialisation Stimulus (bootstrap.js)

```javascript
// assets/bootstrap.js
import { startStimulusApp } from '@symfony/stimulus-bridge';

// Démarre l'application Stimulus et charge automatiquement tous les controllers
// depuis assets/controllers/ (grâce au stimulus-bridge)
export const app = startStimulusApp(
    require.context(
        '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
        true,
        /\.[jt]sx?$/
    )
);
```

### 4.2 Controller de gestion des collections de formulaires

Le controller `form-collection_controller.js` permet l'ajout et la suppression dynamiques de lignes dans un formulaire Symfony `CollectionType` (liste d'items extensible).

```javascript
// assets/controllers/form-collection_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    // Valeurs configurables via data-* attributes
    static values = {
        addLabel: String,    // Texte du bouton "Ajouter"
        deleteLabel: String  // Texte du bouton "Supprimer"
    }

    connect() {
        // Initialise l'index depuis le nombre d'éléments existants
        this.index = this.element.childElementCount;

        // Crée le bouton "Ajouter" et l'injecte dans le DOM
        const btn = document.createElement('button');
        btn.setAttribute('class', 'btn btn-sm btn-outline-primary');
        btn.innerText = this.addLabelValue || 'Ajouter un élément';
        btn.setAttribute('type', 'button');
        btn.addEventListener('click', this.addElement);

        // Ajoute un bouton "Supprimer" sur chaque ligne existante
        this.element.childNodes.forEach(this.addDeleteButton);
        this.element.append(btn);
    }

    // Ajoute une nouvelle ligne depuis le prototype HTML de Symfony
    addElement = (e) => {
        e.preventDefault();

        // Crée un élément HTML depuis le data-prototype (pattern Symfony CollectionType)
        const element = document.createRange()
            .createContextualFragment(
                this.element.dataset['prototype'].replaceAll('__name__', this.index)
            )
            .firstElementChild;

        this.addDeleteButton(element);
        this.index++;
        e.currentTarget.insertAdjacentElement('beforebegin', element);
    }

    // Ajoute un bouton "Supprimer" sur un élément de la collection
    addDeleteButton = (item) => {
        const btn = document.createElement('button');
        btn.setAttribute('class', 'btn btn-sm btn-outline-danger');
        btn.innerText = this.deleteLabelValue || 'Supprimer';
        btn.setAttribute('type', 'button');
        item.append(btn);

        // Supprime l'élément du DOM au clic (pas de rechargement)
        btn.addEventListener('click', e => {
            e.preventDefault();
            item.remove();
        });
    }
}
```

**Utilisation dans un template Twig :**

```twig
{# Utilisation du controller Stimulus via data-* attributes #}
<div data-controller="form-collection"
     data-form-collection-add-label-value="Ajouter une compétence"
     data-form-collection-delete-label-value="Supprimer"
     data-prototype="{{ form_widget(form.competences)|e('html_attr') }}">
    {% for competence in form.competences %}
        <div>{{ include('composants/inputs/collectiontype.html.twig') }}</div>
    {% endfor %}
</div>
```

### 4.3 Controller de protection CSRF

```javascript
// assets/controllers/csrf_protection_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];

    connect() {
        // Génère automatiquement le token CSRF pour les formulaires
        // qui utilisent ce controller
        this.refreshToken();
    }

    refreshToken() {
        // Récupère un nouveau token depuis le endpoint Symfony
        fetch(this.element.dataset.url)
            .then(response => response.json())
            .then(data => {
                this.inputTarget.value = data.token;
            });
    }
}
```

---

## 5. Suppression AJAX avec mise à jour partielle du DOM

L'une des interactions dynamiques clés est la suppression d'une prescription : elle doit se faire sans rechargement complet de la page, et le tableau doit se mettre à jour instantanément.

### 5.1 Côté serveur — Réponse JSON avec HTML partiel

```php
// src/Controller/Gestapp/PrescriptionController.php

#[Route('/{id}/del', name: 'app_gestapp_prescription_del', methods: ['POST'])]
public function del(
    Request $request,
    Prescription $prescription,
    EntityManagerInterface $entityManager,
    PrescriptionRepository $prescriptionRepository
): Response {
    $member = $this->getUser();

    // Suppression des fichiers PDF sur le disque
    $paths = array_filter(
        [$prescription->getPath(), $prescription->getPathSigned(), $prescription->getPathSignedCertif()],
        fn($path) => $path !== null
    );
    foreach ($paths as $p) {
        if (!empty($p) && is_file($this->getParameter('kernel.project_dir')."/public".$p)) {
            unlink($this->getParameter('kernel.project_dir')."/public".$p);
        }
    }

    // Archivage Docuseal si présent
    if ($prescription->getDocuseal()) {
        $docuseal = $prescription->getDocuseal();
        $api = new \Docuseal\Api($this->docuseal_Key, 'https://dseal.openpixl.fr/api');
        $api->archiveSubmission($docuseal->getIdSeal());
        $entityManager->remove($docuseal);
    }

    $entityManager->remove($prescription);
    $entityManager->flush();

    // Recharge la liste selon le rôle
    if (in_array('ROLE_PRESCRIPTEUR', $member->getRoles())) {
        $prescriptions = $prescriptionRepository->findBy(['prescriptor' => $member->getStructure()]);
    }
    if (in_array('ROLE_MEDIATEUR', $member->getRoles())) {
        $prescriptions = $prescriptionRepository->findBy(['lieuMediation' => $member->getStructure()]);
    }
    if (in_array('ROLE_SUPER_ADMIN', $member->getRoles())) {
        $prescriptions = $prescriptionRepository->findAll();
    }

    // Retourne le HTML du tableau mis à jour + message de succès
    return $this->json([
        'code' => 200,
        'message' => 'Prescription supprimée avec succès',
        'liste' => $this->renderView('gestapp/prescription/include/_liste.html.twig', [
            'prescriptions' => $prescriptions,
        ])
    ], 200);
}
```

### 5.2 Côté client — Requête fetch et mise à jour DOM

```javascript
// assets/js/pages/gestapp/prescription/index_prescription.js

export function initIndex_Prescription() {
    // Délégation d'événements : écoute tous les boutons de suppression
    document.addEventListener('click', async function(e) {
        const btnDelete = e.target.closest('[data-action="delete-prescription"]');
        if (!btnDelete) return;

        e.preventDefault();

        const prescriptionId = btnDelete.dataset.id;
        const csrfToken = btnDelete.dataset.token;

        // Requête POST vers le endpoint de suppression
        const response = await fetch(`/gestapp/prescription/${prescriptionId}/del`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                '_token': csrfToken
            })
        });

        const data = await response.json();

        if (data.code === 200) {
            // Mise à jour du tableau sans rechargement de page
            document.getElementById('liste-prescriptions').innerHTML = data.liste;

            // Affichage de la notification toast
            showToast(data.message, 'success');
        }
    });
}
```

---

## 6. Notifications utilisateur — Système Toast

```twig
{# templates/include/toaster.html.twig #}
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toastContainer">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toastTitle">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastBody">
            {# Message injecté dynamiquement via JS #}
        </div>
    </div>
</div>
```

```javascript
// Fonction utilitaire réutilisée dans tous les modules JS
function showToast(message, type = 'info') {
    const toastEl = document.getElementById('liveToast');
    const toastBody = document.getElementById('toastBody');

    toastBody.textContent = message;
    toastEl.className = `toast text-bg-${type}`;  // Bootstrap couleurs : success, danger, info

    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}
```

---

## 7. Navigation SPA-like — Turbo

Turbo (part of Hotwire) remplace les navigations classiques (rechargement complet) par des requêtes AJAX transparentes. En cliquant sur un lien ou soumettant un formulaire, seul le `<body>` est mis à jour.

```javascript
// assets/bootstrap.js — Turbo est démarré automatiquement au chargement
import '@hotwired/turbo';
```

```twig
{# Désactiver Turbo sur un formulaire spécifique si besoin #}
{{ form_start(form, { attr: { 'data-turbo': 'false' } }) }}

{# Ou sur un lien #}
<a href="{{ path('app_logout') }}" data-turbo="false">Se déconnecter</a>
```

---

## 8. Protection CSRF — Intégration JS + PHP

Symfony génère automatiquement des tokens CSRF sur tous les formulaires. Les requêtes AJAX doivent inclure ce token pour être acceptées.

```twig
{# Template : bouton suppression avec token CSRF dans data-* #}
<button type="button"
        data-action="delete-prescription"
        data-id="{{ prescription.id }}"
        data-token="{{ csrf_token('delete' ~ prescription.id) }}"
        class="btn btn-sm btn-outline-danger">
    <i class="fa-duotone fa-solid fa-trash"></i>
</button>
```

```php
// Controller PHP : validation du token CSRF
if ($this->isCsrfTokenValid('delete'.$prescription->getId(), $request->getPayload()->getString('_token'))) {
    $entityManager->remove($prescription);
    $entityManager->flush();
}
```

---

## 9. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| Les interactions sont fluides et sans rechargement complet | Suppression AJAX avec `fetch`, mise à jour DOM partielle, Turbo |
| Les composants JS sont réutilisables | Stimulus controllers (`form-collection`, `csrf_protection`) |
| La communication asynchrone est sécurisée | Tokens CSRF validés côté serveur sur toutes les actions AJAX |
| Le feedback utilisateur est immédiat | Système de notifications Toast Bootstrap |
| L'architecture JS est maintenable | Pattern dispatcher dans app.js, modules par page |
| Les collections de formulaires sont dynamiques | Ajout/suppression de lignes sans rechargement via Stimulus |
| La navigation est optimisée | Turbo pour les navigations SPA-like |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*