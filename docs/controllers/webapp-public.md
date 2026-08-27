# Webapp\PublicController — Pages publiques

`src/Controller/Webapp/PublicController.php`

## Rôle

Sert les pages accessibles sans authentification (aucune entrée dans `access_control` de `security.yaml` pour ces routes). Aucune dépendance injectée.

## Routes

| Méthode HTTP | Chemin | Nom de route | Vue | Lié depuis |
|---|---|---|---|---|
| GET | `/mentions` | `app_webapp_public_mentions` | `webapp/public/mentions.html.twig` | `templates/include/footer.html.twig` |
| GET | `/prezDon` | `app_webapp_public_prezDon` | `webapp/public/prezDon.html.twig` | `templates/include/navbar.html.twig` |
| GET | `/contact` | `app_webapp_public_contact` | `webapp/public/contact.html.twig` | `templates/include/footer.html.twig` |

## Méthodes

- **`mentions()`** — Rend la page des mentions légales.
- **`prezDon()`** — Rend la page de présentation du dispositif de don.
- **`contact()`** — Rend la page de contact.

Les trois méthodes sont de simples rendus de vue statique, sans logique métier ni paramètre. Documentées en PHPDoc dans le code source.

## Code mort

Aucun : les trois routes sont chacune référencées depuis la navbar ou le footer globaux (`templates/include/`), donc bien atteignables dans le parcours utilisateur.
