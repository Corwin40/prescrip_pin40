# CompetenceController — CRUD autonome des compétences

`src/Controller/Gestapp/CompetenceController.php`

## Rôle

CRUD complet et standard pour l'entité `Competence`, très probablement généré via `make:crud`. Préfixe `/gestapp/competence`. Dépendances : `CompetenceRepository`, `EntityManagerInterface`.

## ⚠️ Contrôleur probablement mort dans son ensemble

Aucune route de ce contrôleur (`app_gestapp_competence_index/new/show/edit/delete`) n'est référencée en dehors de ses **propres** vues (`templates/gestapp/competence/*.html.twig`) — vérifié par recherche exhaustive dans `templates/include/`, `templates/admin/`, `templates/gestapp/prescription/` et `assets/js/`. Rien dans la navigation, le dashboard ou le parcours de prescription n'y renvoie.

En pratique, les compétences sont créées et gérées **via le formulaire embarqué de `PrescriptionType`** : `PrescriptionController` fait directement `$prescription->setCompetence(new Competence())` (ligne ~202) dans son propre flux de création. Ce CRUD dédié semble être un reliquat de scaffolding jamais branché à l'application, entièrement doublé par le flux prescription.

**Non supprimé par prudence** (suppression d'un contrôleur entier + ses vues = décision à valider), mais c'est le candidat le plus net rencontré dans cet audit pour une suppression complète.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action |
|---|---|---|---|
| GET | `/gestapp/competence/` | `app_gestapp_competence_index` | Liste des compétences |
| GET/POST | `/gestapp/competence/new` | `app_gestapp_competence_new` | Création |
| GET | `/gestapp/competence/{id}` | `app_gestapp_competence_show` | Fiche détail |
| GET/POST | `/gestapp/competence/{id}/edit` | `app_gestapp_competence_edit` | Édition |
| POST | `/gestapp/competence/{id}` | `app_gestapp_competence_delete` | Suppression |

## Méthodes

CRUD Doctrine standard, sans logique métier particulière — `index()`, `new()`, `show()`, `edit()`, `delete()`. PHPDoc ajouté sur chaque méthode dans le code source.

## Code mort

Aucun code mort *à l'intérieur* des méthodes — le souci est le contrôleur entier, voir ci-dessus.
