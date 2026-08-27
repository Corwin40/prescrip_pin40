# SetupController — Première installation

`src/Controller/Admin/SetupController.php`

## Rôle

Assistant de première installation de l'application : permet de créer le tout premier compte, forcément `ROLE_SUPER_ADMIN`, quand la base de membres est vide. Dépendances injectées : `MemberRepository`, `UserPasswordHasherInterface`, `EntityManagerInterface`, `Security` (Symfony Security Bundle, pour connecter directement le compte créé).

## Routes

| Méthode HTTP | Chemin | Nom de route | Action |
|---|---|---|---|
| GET/POST | `/setup` | `app_setup` | Formulaire de création du premier compte |

## Méthodes

- **`create(Request, MemberRepository, UserPasswordHasherInterface, EntityManagerInterface, Security)`** — Garde-fou en tête de méthode : si `MemberRepository::count([]) > 0` (au moins un membre existe déjà), redirige immédiatement vers `/login` — cet écran ne doit exister que tant qu'aucun compte n'existe. Sinon, affiche/traite le formulaire `FirstAccountType`, force le rôle `ROLE_SUPER_ADMIN` et `isVerified = true` sur le membre créé, puis le connecte directement via `Security::login($member, 'form_login', 'main')` (pas de redirection vers `/login`, l'utilisateur est authentifié immédiatement après la création).

## Code mort

Aucun.

## Observation

Aucun lien vers `/setup` dans l'application (ni template ni JS) — cohérent avec son rôle : c'est un écran d'amorçage à visiter manuellement une seule fois avant que le premier compte n'existe, pas une page destinée à être liée dans la navigation.
