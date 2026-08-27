# DocumentController — Documents de prescription

`src/Controller/Gestapp/DocumentController.php`

## Rôle

Gère le dépôt manuel de documents de prescription signée, en alternative au flux de signature électronique Docuseal (voir [admin-docuseal.md](./admin-docuseal.md)). Dépendances : `PrescriptionRepository`, `EntityManagerInterface`, `LoggerInterface`.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action | Utilisée depuis |
|---|---|---|---|---|
| GET (implicite) | `/gestapp/document` | `app_gestapp_document` | Page scaffold par défaut | **Aucune** — voir Observations |
| GET/POST (implicite) | `/gestapp/document/prescription_signed_manually/{idprescription}` | `app_gestapp_document_prescriptionsigned_manually` | Upload manuel du document signé | `templates/gestapp/prescription/include/badge.html.twig` |

## Méthodes

- **`index()`** — Rend `gestapp/document/index.html.twig` avec le seul contexte `controller_name`. C'est le squelette généré par défaut par Maker Bundle, jamais personnalisé ni relié à l'application (voir Observations).
- **`prescription_signed_manually(Request, $idprescription, PrescriptionRepository, EntityManagerInterface)`** — Dépôt manuel d'un PDF signé pour une prescription : déplace le fichier uploadé vers `prescription_signed_directory` (crée le dossier de la structure si besoin), crée l'entité `Document` liée, fait passer la prescription à l'étape `Signed`. Répond toujours en JSON (appelée en AJAX depuis la modale d'upload) : succès renvoie aussi la liste de prescriptions HTML mise à jour et filtrée par rôle ; formulaire invalide renvoie le formulaire ré-rendu (code métier 400, HTTP 200) ; GET initial renvoie le formulaire vierge (code 200). Les échecs de déplacement de fichier sont désormais journalisés (voir Corrections).

## Code mort

Aucune ligne isolée (pas d'import inutilisé, pas de debug oublié). Voir cependant l'observation ci-dessous sur `index()`.

## Corrections apportées

- **Gestion d'erreur du déplacement de fichier corrigée** : le `catch (FileException $e) { }` vide (l'exception réellement levée par `UploadedFile::move()` en cas d'échec) logge désormais l'échec en `error` via un `LoggerInterface` injecté au contrôleur, avec la référence de prescription et le chemin visé. Comportement fonctionnel inchangé (le flux continue et l'entité `Document` est quand même persistée), seule la visibilité de l'échec change.
- **Bug de statut HTTP corrigé sur le retour d'erreur de formulaire** : la réponse `{code: 400, ...}` était renvoyée avec un vrai statut HTTP 400, ce qui fait basculer axios sur `.catch()` côté client au lieu de `.then()` — le formulaire ré-rendu avec ses erreurs n'était donc jamais affiché à l'utilisateur (confirmé en lisant `assets/js/pages/admin/dashboard/index.js`, qui teste explicitement `data.code === 400` dans son `.then()`, code mort côté JS à cause de ce bug). Réponse renvoyée en HTTP 200 désormais, le code d'erreur restant porté par le champ `code` du JSON.

## Observations

- **`index()` (route `app_gestapp_document`) probablement morte** : c'est le contenu par défaut généré par le Maker Bundle Symfony (`'controller_name' => 'DocumentController'`, motif caractéristique du scaffolding jamais retouché), et aucune vue ni script du projet n'y renvoie (recherche exhaustive avec délimitation de mot pour éviter la confusion avec `app_gestapp_document_prescriptionsigned_manually`, qui partage le même préfixe). Non supprimée par prudence — candidat clair à un nettoyage si confirmé inutile.
