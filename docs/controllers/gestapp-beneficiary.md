# BeneficiaryController — Gestion des bénéficiaires

`src/Controller/Gestapp/BeneficiaryController.php`

## Rôle

Gère les bénéficiaires (`App\Entity\Gestapp\Beneficiary`) : recherche via Elasticsearch, création (deux flux différents), consultation, édition, suppression. Préfixe de route `/gestapp/beneficiary`.

Dépendances injectées : `PaginatedFinderInterface` (FOSElasticaBundle, via le constructeur), plus par méthode `StructureRepository`, `EntityManagerInterface`, l'index Elasticsearch `fos_elastica.index.beneficiary` (autowired).

## Routes

| Méthode HTTP | Chemin | Nom de route | Action | Utilisée depuis |
|---|---|---|---|---|
| GET/POST | `/gestapp/beneficiary/` | `app_gestapp_beneficiary_index` | Recherche (Elasticsearch) — vraie page de liste | Nombreuses vues |
| GET | `/gestapp/beneficiary/await` | `app_gestapp_beneficiary_await` | Liste via Doctrine classique | **Aucune** — voir Observations |
| GET/POST | `/gestapp/beneficiary/new` | `app_gestapp_beneficiary_new` | Création classique (page dédiée) | `new.html.twig`, `edit.html.twig`, `search.html.twig`, `index.html.twig`, dashboard, `show.html.twig` |
| GET/POST | `/gestapp/beneficiary/new2` | `app_gestapp_beneficiary_new2` | Création en AJAX (modale, depuis le formulaire de prescription) | `gestapp/prescription/include/_beneficiaryForm.html.twig` |
| GET | `/gestapp/beneficiary/{id}` | `app_gestapp_beneficiary_show` | Fiche détail | `search.html.twig`, `index.html.twig` |
| GET/POST | `/gestapp/beneficiary/{id}/edit` | `app_gestapp_beneficiary_edit` | Édition | `search.html.twig`, `_form.html.twig`, `index.html.twig` |
| POST | `/gestapp/beneficiary/{id}/delete` | `app_gestapp_beneficiary_delete` | Suppression | `index.html.twig`, `search.html.twig`, `_delete_form.html.twig` |

## Méthodes

- **`search(Request, StructureRepository)`** *(route `app_gestapp_beneficiary_index`, méthode nommée `search`)* — Recherche via Elasticsearch (FOSElasticaBundle) : combine un filtre automatique selon le rôle (prescripteur → sa structure ; médiateur → les structures qu'il médie ; super-admin → toutes) avec les filtres du formulaire `BeneficiarySearchType` (texte sur prénom/nom, structure). C'est la page de liste réellement utilisée.
- **`index(BeneficiaryRepository)`** *(route `app_gestapp_beneficiary_await`)* — Liste via une requête Doctrine classique, filtrée par rôle. Nommage historique trompeur (méthode `index()` mais route `/await`, alors que le vrai "index" fonctionnel est `search()`). Non référencée dans l'UI, voir Observations.
- **`new(Request, EntityManagerInterface, Index $beneficiaryIndex)`** — Création classique via page dédiée. Déduit le genre à partir de la civilité, rattache à la structure choisie (par défaut celle de l'utilisateur), assigne un référent (responsable de structure pour un rôle admin/prescripteur, sinon l'utilisateur lui-même). Force `$beneficiaryIndex->refresh()` après persist pour une visibilité immédiate en recherche (sinon délai near-real-time d'Elasticsearch).
- **`new2(Request, EntityManagerInterface)`** — Création en AJAX depuis la modale du formulaire de prescription. Répond toujours en JSON : succès avec les données pour peupler le `<select>` bénéficiaire côté prescription, erreur (422) avec le formulaire ré-rendu en HTML.
- **`show(Beneficiary)`** — Fiche détail.
- **`edit(Request, Beneficiary, EntityManagerInterface)`** — Édition.
- **`delete(Request, Beneficiary, EntityManagerInterface)`** — Supprime d'abord la prescription liée si elle existe (évite une prescription orpheline), puis le bénéficiaire. Aucune restriction de rôle explicite sur cette action.

## Code mort traité

- **Import inutilisé supprimé** : `use Elastica\Query\Nested;` — jamais utilisé dans le fichier (seuls `BoolQuery`, `MultiMatch`, `Term`, `Terms` le sont).
- **Appels de debug commentés supprimés** : `//dd($beneficiary);` dans `new2()`, `//dd($beneficiary,$form, $user);` dans `edit()`.

## Observations

- **`index()` (route `/await`) probablement morte** : aucune vue ni script JS du projet ne référence `app_gestapp_beneficiary_await` (recherche exhaustive dans `templates/` et `assets/js/`). Le nommage suggère un reliquat d'une ancienne liste (avant l'introduction de la recherche Elasticsearch `search()`), jamais nettoyé. Non supprimée par prudence — à confirmer.
- **Deux flux de création qui se recoupent** (`new()` et `new2()`) : logique métier proche (genre déduit de la civilité, référent assigné selon le rôle) mais dupliquée avec de légères variances (`new()` répond en HTML/redirection, `new2()` en JSON pour la modale). Pas du code mort — les deux sont utilisés — mais un facteur de duplication à surveiller si la logique métier évolue.
