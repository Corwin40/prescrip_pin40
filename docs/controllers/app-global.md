# AppController — Point d'entrée de l'application

`src/Controller/AppController.php`

## Rôle

Point d'entrée racine (`/`) de l'application. Ne rend aucune vue : redirige systématiquement vers le tableau de bord (`app_admin_dashboard_index`), qui applique ensuite sa propre logique d'accès selon que l'utilisateur est authentifié ou non.

Aucune dépendance injectée.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action |
|---|---|---|---|
| GET | `/` | `app_ppin40_entry` | Redirection vers le dashboard |

## Méthodes

- **`index()`** — Redirige inconditionnellement vers `app_admin_dashboard_index`. Documenté en PHPDoc dans le code source.

## Code mort

Aucun dans le contrôleur lui-même (une seule méthode, sans logique).

## Observation

Le template `templates/app/index.html.twig` (page de bienvenue par défaut du squelette Symfony) existe toujours dans le dépôt mais **n'est rendu par aucun contrôleur** — `AppController::index()` redirige, il ne rend jamais de vue. C'est un résidu du gabarit initial du projet, sans usage fonctionnel. Candidat à suppression si confirmé inutile ailleurs (composant inclus nulle part).
