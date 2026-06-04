# Helpers Bootstrap Modal — `modal.js`

## Contexte

Plusieurs pages du module `gestapp` partagent le même pattern Bootstrap Modal :
initialisation, parsing du déclencheur, affichage d'un contenu iframe, et binding des
événements. Ces utilitaires factorisent ce code répété.

**Fichier source** : `assets/js/components/bootstrap/modal.js`

---

## Prérequis HTML

### L'élément modal

Toutes les fonctions attendent un élément modal avec l'`id="modal"` (par défaut) et la
structure Bootstrap standard :

```html
<div class="modal fade" id="modal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"></h5>
      </div>
      <div class="modal-body"></div>
      <div class="modal-footer">
        <a href="#" class="btn btn-outline-primary">Confirmer</a>
      </div>
    </div>
  </div>
</div>
```

### Les déclencheurs

Chaque lien déclencheur doit avoir la classe `.openModal` et un attribut `data-bs-data`
au format `CRUD_TYPE-Titre affiché-option` :

```html
<a href="/prescription/42/voir"
   class="openModal"
   data-bs-data="VIEW_PRESCRIPTION-Voir la prescription-">
  Voir
</a>
```

Le bouton de soumission du modal doit avoir l'`id="btnSubmitModal"` :

```html
<button id="btnSubmitModal" type="button" class="btn btn-primary">Valider</button>
```

---

## Fonctions

### `initModal(id = 'modal')`

Initialise l'instance Bootstrap Modal et retourne les deux objets nécessaires.

**Paramètre**

| Nom | Type | Défaut | Description |
|-----|------|--------|-------------|
| `id` | `string` | `'modal'` | L'`id` de l'élément modal dans le DOM |

**Retour** : `{ modalEl, modal }` ou `null` si l'élément est absent de la page.

**Usage type**

```js
const modalCtx = initModal();
if (!modalCtx) return;          // guard : la page n'a pas de modal
const { modalEl, modal } = modalCtx;
```

---

### `parseModalTrigger(e)`

Extrait les données portées par le lien déclencheur (`.openModal`) depuis l'événement
click. Appelle automatiquement `e.preventDefault()`.

**Paramètre**

| Nom | Type | Description |
|-----|------|-------------|
| `e` | `Event` | L'événement click sur le déclencheur |

**Retour** : objet `{ url, crud, contentTitle, option }`

| Propriété | Source | Exemple |
|-----------|--------|---------|
| `url` | `a.href` | `"/prescription/42/voir"` |
| `crud` | 1er segment de `data-bs-data` | `"VIEW_PRESCRIPTION"` |
| `contentTitle` | 2e segment de `data-bs-data` | `"Voir la prescription"` |
| `option` | 3e segment de `data-bs-data` | `""` |

**Usage type**

```js
function openModal(e) {
    const { url, crud, contentTitle } = parseModalTrigger(e);

    if (crud === 'VIEW_PRESCRIPTION') { ... }
    else if (crud === 'DEL_PRESCRIPTION') { ... }
}
```

---

### `setupIframeModal(modalEl, modal, url)`

Configure et affiche le modal en mode **plein écran iframe** : masque le titre et le
bouton de confirmation, injecte une `<iframe>` qui charge `url`.

**Paramètres**

| Nom | Type | Description |
|-----|------|-------------|
| `modalEl` | `HTMLElement` | L'élément modal (issu de `initModal`) |
| `modal` | `bootstrap.Modal` | L'instance Bootstrap Modal |
| `url` | `string` | L'URL à charger dans l'iframe |

**Effets**

- `.modal-title` → `d-none`
- `.modal-dialog` → `modal-xl`
- `.modal-body` → `p-0` + `<iframe>` de 600px de hauteur
- `.modal-footer a` → `d-none`
- Appelle `modal.show()`

**Usage type**

```js
if (crud === 'VIEW_PRESCRIPTION') {
    setupIframeModal(modalEl, modal, url);
}
```

---

### `bindModalEvents(openFn, submitFn = null)`

Attache les écouteurs d'événements sur tous les déclencheurs `.openModal` et sur le
bouton `#btnSubmitModal`. À appeler à l'initialisation de la page **et** après chaque
mise à jour du DOM (rechargement de liste, réponse Ajax).

**Paramètres**

| Nom | Type | Défaut | Description |
|-----|------|--------|-------------|
| `openFn` | `Function` | — | Handler click pour les liens `.openModal` |
| `submitFn` | `Function\|null` | `null` | Handler click pour `#btnSubmitModal` (omis si la page n'a pas de bouton de soumission) |

**Usage type**

```js
// Initialisation
bindModalEvents(openModal, submitModal);

// Après une réponse Ajax qui recharge le DOM
axios.post(url).then(() => {
    bindModalEvents(openModal, submitModal);
});

// Page sans bouton de soumission
bindModalEvents(openModal);
```

---

### `removeOptions(selectElement)`

Vide toutes les options d'un élément `<select>`.

**Paramètre**

| Nom | Type | Description |
|-----|------|-------------|
| `selectElement` | `HTMLSelectElement` | Le `<select>` à vider |

**Usage type**

```js
const select = document.getElementById('prescription_beneficiaire');
removeOptions(select);
select.options.add(new Option(data.label, data.value));
```

---

## Pattern complet — exemple d'une page

```js
import { initModal, parseModalTrigger, setupIframeModal, bindModalEvents, removeOptions }
    from '../../../components/bootstrap/modal';
import { toasterMessage } from '../../../components/bootstrap/toaster';
import axios from 'axios';

export function initMaPage() {
    const modalCtx = initModal();
    if (!modalCtx) return;
    const { modalEl, modal } = modalCtx;

    function openModal(e) {
        const { url, crud, contentTitle } = parseModalTrigger(e);

        if (crud === 'VIEW_ITEM') {
            setupIframeModal(modalEl, modal, url);
        }
        else if (crud === 'DEL_ITEM') {
            modalEl.querySelector('.modal-title').innerText = contentTitle;
            // ...configuration spécifique à la page...
            modal.show();
        }
        else {
            bindModalEvents(openModal, submitModal);
            toasterMessage('une erreur est survenue');
        }
    }

    function submitModal(e) {
        e.preventDefault();
        // ...logique métier...
        modal.hide();
        bindModalEvents(openModal, submitModal);
    }

    bindModalEvents(openModal, submitModal);
}
```

---

## Pages utilisant ce composant

| Fichier | Fonctions importées |
|---------|---------------------|
| `pages/gestapp/prescription/index_prescription.js` | `initModal`, `parseModalTrigger`, `setupIframeModal`, `bindModalEvents`, `removeOptions` |
| `pages/gestapp/prescription/newedit_prescription.js` | `initModal`, `parseModalTrigger`, `bindModalEvents`, `removeOptions` |
| `pages/gestapp/prescription/adminListPrescription.js` | `initModal`, `parseModalTrigger`, `setupIframeModal`, `bindModalEvents` |
| `pages/gestapp/beneficiary/indexBeneficiary.js` | `parseModalTrigger`, `bindModalEvents` |
