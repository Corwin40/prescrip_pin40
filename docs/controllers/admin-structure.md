# StructureController — Gestion des structures

`src/Controller/Admin/StructureController.php`

## Rôle

Gère les structures (prescripteurs/médiateurs). Contrôleur entièrement réservé à `ROLE_ADMIN` (`#[IsGranted('ROLE_ADMIN')]` au niveau classe — couvre aussi `ROLE_SUPER_ADMIN` via la hiérarchie de `security.yaml`). Préfixe de route `/admin/structure`.

Dépendances injectées : `StructureRepository`, `EntityManagerInterface`, `UserPasswordHasherInterface`, `SluggerInterface`. Utilise aussi Symfony UX Map (Leaflet) pour l'affichage cartographique.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action |
|---|---|---|---|
| GET | `/admin/structure/` | `app_admin_structure_index` | Liste des structures + carte Leaflet |
| GET/POST | `/admin/structure/new` | `app_admin_structure_new` | Création d'une structure + de son membre responsable |
| GET/POST | `/admin/structure/{id}/edit` | `app_admin_structure_edit` | Édition d'une structure |

## Méthodes

- **`index(StructureRepository)`** — Liste toutes les structures et construit une carte Leaflet (Symfony UX Map) centrée sur la région Nouvelle-Aquitaine (coordonnées `43.88478, -0.50400`, zoom 9), avec le fond de carte OpenStreetMap.
- **`new(Request, EntityManagerInterface, UserPasswordHasherInterface, SluggerInterface)`** — Crée une structure **et** son membre responsable en une seule soumission : le formulaire `StructureType` embarque un sous-formulaire membre (`form->get('member')`). Le membre créé reçoit le rôle choisi dans le formulaire, un mot de passe hashé, et `isRespStructure = true`.
- **`edit(Request, Structure, EntityManagerInterface, SluggerInterface)`** — Édite une structure existante ; ne touche pas au membre responsable (contrairement à `new()`).

Dans les deux cas création/édition, le slug de la structure est régénéré à partir de son nom à chaque soumission valide.

## Code mort traité

- **Import inutilisé supprimé** : `use Symfony\Component\PasswordHasher\PasswordHasherInterface;` — seule `UserPasswordHasherInterface` (import distinct) est réellement utilisée.
- **Variables inutilisées supprimées** : `$user = $this->getUser();` dans `index()` et dans `new()`, assignées mais jamais lues (l'accès est déjà filtré par `#[IsGranted('ROLE_ADMIN')]` au niveau classe, ces appels étaient probablement des reliquats d'une vérification manuelle antérieure).

## Observations

- **Fonctionnalité signalée comme incomplète par un commentaire du code** : dans `new()`, un commentaire indique *"dans la poursuite de la création d'une structure, il faut créer obligatoirement le dossier de stockage des pdfs générés et déposés"* — la création du dossier de stockage des PDF pour la nouvelle structure n'est pas implémentée à cet endroit. À vérifier si elle est faite ailleurs (à la volée lors de la première génération de PDF) ou si c'est un manque réel.
