# EquipmentController — Gestion des équipements

`src/Controller/Gestapp/EquipmentController.php`

## Rôle

CRUD standard pour l'entité `Equipment` (matériel prêté aux bénéficiaires dans le cadre d'une prescription). Préfixe `/gestapp/equipment`. Dépendances : `EquipmentRepository`, `EntityManagerInterface`.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action | Utilisée depuis |
|---|---|---|---|---|
| GET | `/gestapp/equipment/` | `app_gestapp_equipment_index` | Liste des équipements | `admin/dashboard/index.html.twig`, `gestapp/equipment/*` |
| GET/POST | `/gestapp/equipment/new` | `app_gestapp_equipment_new` | Création | `admin/dashboard/index.html.twig` |
| GET | `/gestapp/equipment/{id}` | `app_gestapp_equipment_show` | Fiche détail | `gestapp/equipment/index.html.twig` |
| GET/POST | `/gestapp/equipment/{id}/edit` | `app_gestapp_equipment_edit` | Édition | `gestapp/equipment/show.html.twig` |
| POST | `/gestapp/equipment/{id}` | `app_gestapp_equipment_delete` | Suppression | `gestapp/equipment/_delete_form.html.twig` |

## Méthodes

CRUD Doctrine standard, sans logique métier particulière — `index()`, `new()`, `show()`, `edit()`, `delete()`. PHPDoc ajouté sur chaque méthode dans le code source. Contrairement à `CompetenceController`, ce contrôleur est bien relié à l'application : la liste et la création sont accessibles depuis le tableau de bord admin (`EquipmentRepository::findByDispos()` y est aussi utilisé, voir [admin-dashboard.md](./admin-dashboard.md)).

## Code mort

Aucun.
