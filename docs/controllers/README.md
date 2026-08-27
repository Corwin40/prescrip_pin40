# Documentation technique des contrôleurs

Analyse du code actuel (pas un historique Git) : rôle de chaque contrôleur, routes, méthodes documentées, code mort traité, observations restant à trancher. Un fichier par contrôleur.

## App\Controller

- [app-global.md](./app-global.md) — `AppController` (point d'entrée `/`)

## App\Controller\Admin

- [admin-dashboard.md](./admin-dashboard.md) — `DashboardController`
- [admin-docuseal.md](./admin-docuseal.md) — `DocusealController` (signature électronique)
- [admin-member.md](./admin-member.md) — `MemberController` (gestion des membres)
- [admin-security.md](./admin-security.md) — `SecurityController` (authentification) — **bug corrigé** : chemin de template cassé sur `/login`
- [admin-setup.md](./admin-setup.md) — `SetupController` (première installation)
- [admin-structure.md](./admin-structure.md) — `StructureController` (gestion des structures)

## App\Controller\Gestapp

- [gestapp-beneficiary.md](./gestapp-beneficiary.md) — `BeneficiaryController`
- [gestapp-competence.md](./gestapp-competence.md) — `CompetenceController` — **contrôleur probablement mort dans son ensemble**
- [gestapp-document.md](./gestapp-document.md) — `DocumentController`
- [gestapp-equipment.md](./gestapp-equipment.md) — `EquipmentController`
- [gestapp-htmltopdf.md](./gestapp-htmltopdf.md) — `HtmlToPdfController` (génération PDF via Gotenberg)
- [gestapp-prescription.md](./gestapp-prescription.md) — `PrescriptionController` (workflow central)

## App\Controller\Webapp

- [webapp-public.md](./webapp-public.md) — `PublicController` (pages publiques)

---

## Synthèse du code mort traité

Code supprimé (imports inutilisés, appels de debug commentés, variables/méthodes strictement inatteignables) :

| Contrôleur | Éléments supprimés |
|---|---|
| `MemberController` | 5 imports inutilisés, 2 appels `dd()` commentés |
| `DashboardController` | 1 import inutilisé, 2 variables inutilisées, 1 appel `dd()` commenté |
| `DocusealController` | 2 appels `dd()` commentés, lignes vides superflues |
| `StructureController` | 1 import inutilisé, 2 variables inutilisées |
| `BeneficiaryController` | 1 import inutilisé, 2 appels `dd()` commentés |
| `HtmlToPdfController` | 2 imports inutilisés |
| `PrescriptionController` | 1 import inutilisé, 3 méthodes entièrement mortes, 1 variable inutilisée, 1 appel `dd()` commenté, 1 bloc de code commenté |

Un vrai **bug corrigé** (pas du code mort) : `SecurityController::login()` rendait un chemin de template avec un antislash au lieu d'un slash (`admin\security/login.html.twig`), cassant le rendu de la page de connexion.

## Corrections apportées (suite à l'audit)

Trois points relevés en observations ont été corrigés dans une passe ultérieure :

- **CSRF ajouté sur `PrescriptionController::del()`** (la route de suppression réellement utilisée par l'UI, jusque-là non protégée) — câblage complet Twig → JS → contrôleur. Détail dans [gestapp-prescription.md](./gestapp-prescription.md#correction-de-sécurité).
- **Email codé en dur remplacé** dans `DocusealController::prescription()` : l'adresse personnelle du développeur est remplacée par l'email de contact réel du prescripteur (`$element['Email_Prescripteur']`, déjà calculé et déjà utilisé ailleurs dans le même appel). Détail dans [admin-docuseal.md](./admin-docuseal.md#corrections-apportées).
- **Gestion d'erreur des uploads/écritures de fichiers** dans `DocusealController` et `DocumentController` : les `catch` vides logguent désormais l'échec (`LoggerInterface` injecté dans les deux contrôleurs), et dans `DocusealController` le retour de `file_put_contents()` (qui ne lève jamais d'exception, contrairement à ce que laissait penser le `catch (FileException $e)`) est maintenant vérifié explicitement. Détail dans [admin-docuseal.md](./admin-docuseal.md#corrections-apportées) et [gestapp-document.md](./gestapp-document.md#corrections-apportées).

En creusant le premier point, deux bugs de statut HTTP de la même famille ont été trouvés et corrigés en passant (`PrescriptionController::del()` et `DocumentController::prescription_signed_manually()`) : les réponses d'erreur AJAX étaient renvoyées avec un vrai statut HTTP 400/422, ce qui fait qu'axios bascule sur `.catch()` (vide) au lieu de `.then()` côté client — l'utilisateur ne voyait donc jamais le message d'erreur ni le formulaire ré-affiché. Toutes ces routes répondent maintenant en HTTP 200 avec le code d'erreur porté par le champ `code` du JSON, comme le fait déjà le reste du code de ces contrôleurs.

## Fonctionnalités signalées comme probablement mortes (non supprimées)

Ce sont des routes/méthodes/contrôleurs entiers, atteignables par URL directe mais non liés depuis aucune vue ni script du projet. Non supprimés unilatéralement — décision à valider :

- `CompetenceController` **(contrôleur entier, 5 routes)** — doublé par le formulaire de compétence embarqué dans `PrescriptionType`.
- `MemberController::show()`
- `BeneficiaryController::index()` (route `/await`)
- `DocumentController::index()` (page scaffold par défaut)
- `HtmlToPdfController::regeneratePrescriptionPdf()` et `::preview()`
- `PrescriptionController::await()`, `::show()`, `::closedcase()` (ce dernier avec son template associé, jamais inclus nulle part)

## Autres observations notables

- `StructureController::new()` : un commentaire du code lui-même signale que la création du dossier de stockage des PDF pour une nouvelle structure n'est pas implémentée à cet endroit.
