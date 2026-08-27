# DashboardController — Tableau de bord

`src/Controller/Admin/DashboardController.php`

## Rôle

Agrège les données du tableau de bord (bénéficiaires, prescriptions en cours/signées, équipements disponibles), avec un périmètre variable selon le rôle. C'est aussi la cible de la redirection racine (`app_ppin40_entry`, voir [app-global.md](./app-global.md)) et de `login_path`/`default_target_path` (voir [admin-security.md](./admin-security.md)).

Dépendances injectées : `MemberRepository`, `EquipmentRepository`, `PrescriptionRepository`, `BeneficiaryRepository`.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action |
|---|---|---|---|
| GET | `/admin/dashboard` | `app_admin_dashboard_index` | Vue d'ensemble filtrée par rôle |

## Méthodes

- **`index(MemberRepository, EquipmentRepository, PrescriptionRepository, BeneficiaryRepository)`** — Quatre branches selon le rôle de l'utilisateur connecté :
  - `ROLE_PRESCRIPTEUR` : bénéficiaires/prescriptions/équipements limités à sa structure (`$user->getStructure()`).
  - `ROLE_MEDIATEUR` : idem, filtré côté médiation (`findByMediation`).
  - `ROLE_ADMIN` et `ROLE_SUPER_ADMIN` : portée globale (`findAll()` / requêtes non filtrées).

  Les prescriptions sont scindées en deux jeux : celles pas encore à l'étape `Signed` (`filteredByWithoutStep*`) et celles déjà signées (`filteredByStep*`).

## Code mort traité

- **Variables inutilisées supprimées** : `$structure = $user->getStructure();` et `$roles = $user->getRoles();`, assignées en tête de méthode mais jamais lues ensuite (chaque branche relit `$user->getStructure()`/`$user->getRoles()` directement).
- **Import inutilisé supprimé** : `use App\Entity\Gestapp\Prescription;` — la classe `Prescription` n'était référencée nulle part dans le fichier.
- **Appel de debug commenté supprimé** : `//dd($member,$equipments, $prescriptions, $beneficiaries);` en fin de méthode.

## Observations

- **Incohérence mineure, sans impact fonctionnel** : la branche `ROLE_ADMIN` appelle `filteredByWithoutStep(StepPrescription::Signed)` / `filteredByStep(StepPrescription::Signed)` (l'enum lui-même), tandis que la branche `ROLE_SUPER_ADMIN` appelle les mêmes méthodes avec `StepPrescription::Signed->name` (la chaîne `'Signed'`). Dans ce cas précis, l'enum `StepPrescription` est *backed* sur des chaînes identiques à leurs noms de cas (`case Signed = 'Signed'`), donc le résultat est identique — mais c'est une incohérence de style qui mériterait d'être harmonisée (toujours passer l'enum, pas sa représentation `->name`).
- Les vérifications `$user && in_array(...)` dans chaque branche sont redondantes : `$user->getId()` est déjà déréférencé plus haut sans garde, donc `$user` est nécessairement non-null à ce stade (la route est de toute façon protégée par `access_control` pour les rôles admin/médiateur/prescripteur). Non modifié pour rester conservateur — ce n'est pas du code mort, juste une garde redondante sans risque.
