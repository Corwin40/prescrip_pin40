# SecurityController — Authentification

`src/Controller/Admin/SecurityController.php`

## Rôle

Contrôleur d'authentification standard généré par le Maker Bundle Symfony. Fournit les routes `login_path`/`check_path`/`logout` déclarées dans le firewall `main` de `config/packages/security.yaml`. Aucune dépendance métier injectée (seulement `AuthenticationUtils`, service Symfony).

## Routes

| Méthode HTTP | Chemin | Nom de route | Action |
|---|---|---|---|
| GET/POST (implicite) | `/login` | `app_login` | Affiche le formulaire de connexion / point d'entrée `check_path` |
| — | `/logout` | `app_logout` | Interceptée par le firewall, jamais exécutée |

## Méthodes

- **`login(AuthenticationUtils $authenticationUtils)`** — Sert de `login_path` **et** `check_path` au firewall `form_login`. En GET, affiche `admin/security/login.html.twig` avec la dernière erreur d'authentification et le dernier nom d'utilisateur saisi (fournis par `AuthenticationUtils`).
- **`logout()`** — Corps volontairement inatteignable : `/logout` est interceptée par la clé `logout` du firewall avant d'arriver au contrôleur. La méthode ne sert qu'à déclarer la route pour `path('app_logout')` dans les vues.

## Bug corrigé

Le chemin du template passé à `render()` utilisait un **antislash** (`'admin\security/login.html.twig'`) au lieu d'un slash. Sous le loader Twig, ce n'est pas un séparateur de dossier valide : Twig cherchait un fichier littéralement nommé `admin\security` et levait une `Twig\Error\LoaderError` (confirmé via `php bin/console debug:twig`). **Toute requête GET vers `/login` était donc cassée.** Corrigé en `'admin/security/login.html.twig'`, vérifié avec `debug:twig` (template désormais trouvé) et `php -l`.

## Code mort

Aucun — les deux méthodes sont utilisées par le firewall, conformément au fonctionnement standard du `SecurityController` généré par Symfony.
