# PrescriptionController — Workflow de prescription

`src/Controller/Gestapp/PrescriptionController.php`

## Rôle

Contrôleur central de l'application : recherche, création, édition par étapes (machine à états `StepPrescription`), clôture, suppression des prescriptions. Préfixe `/gestapp/prescription`. Dépendances : `PaginatedFinderInterface` (Elasticsearch, via constructeur), `$docuseal_Key` (paramètre), plus par méthode `StructureRepository`, `EntityManagerInterface`, `PrescriptionRepository`, l'index Elasticsearch `fos_elastica.index.prescription` (autowired).

Le cœur du workflow est `edit()` : chaque soumission du formulaire fait avancer la prescription d'une étape (`StepPrescription::Open → OneParts/TwoParts → ChoiceEquipment → ValidCase → ...`), la transition autorisée dépendant à la fois de l'étape courante **et** du rôle de l'utilisateur qui soumet.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action | Utilisée depuis |
|---|---|---|---|---|
| GET/POST | `/gestapp/prescription/` | `app_gestapp_prescription_index` | Recherche (Elasticsearch) — vraie page de liste | Nombreuses vues |
| GET/POST | `/gestapp/prescription/admin` | `app_gestapp_prescription_foradmin` | Liste des prescriptions signées | `templates/include/navbar.html.twig` |
| GET | `/gestapp/prescription/` (nom) | `app_gestapp_prescription_await` | Liste via Doctrine classique | **Aucune** — voir Observations |
| GET/POST | `/gestapp/prescription/new` | `app_gestapp_prescription_new` | Création | Nombreuses vues |
| GET | `/gestapp/prescription/{id}` | `app_gestapp_prescription_show` | Fiche détail | **Aucune** — voir Observations |
| GET/POST | `/gestapp/prescription/{id}/edit` | `app_gestapp_prescription_edit` | Fait avancer la prescription d'une étape | Nombreuses vues |
| POST | `/gestapp/prescription/{id}/closedcase` | `app_gestapp_prescription_closedcase` | Clôture la prescription | **Aucune** — voir Observations |
| POST | `/gestapp/prescription/{id}` | `app_gestapp_prescription_delete` | Suppression classique | `_delete_form.html.twig`, `index.html.twig`, `show.html.twig` |
| POST | `/gestapp/prescription/{id}/del` | `app_gestapp_prescription_del` | Suppression complète, protégée CSRF (AJAX, avec nettoyage fichiers + Docuseal) | `index.html.twig`, `_delete_form.html.twig`, dashboard, `_liste.html.twig` |

## Méthodes

- **`createRef($structure, PrescriptionRepository)`** *(helper interne, appelé par `new()`)* — Génère la référence provisoire (`YYYYMMDD-xxxxx-N`) au moment de la création, avant qu'un prescripteur ne soit connu.
- **`index(Request, StructureRepository)`** *(route `app_gestapp_prescription_index`)* — Recherche Elasticsearch combinant filtre de rôle et filtres du formulaire `PrescriptionSearchType`. C'est la vraie page de liste.
- **`listPrescriptionForAdmin(PrescriptionRepository)`** — Liste les prescriptions à l'étape `Signed`, toutes structures confondues. Accessible depuis la navbar.
- **`await(PrescriptionRepository)`** *(route `app_gestapp_prescription_await`)* — Liste via Doctrine classique, filtrée par rôle. Non référencée dans l'UI, voir Observations.
- **`new(Request, EntityManagerInterface, PrescriptionRepository, Index)`** — Crée une prescription avec un statut/étape de départ selon le rôle. À la soumission, recalcule la référence définitive avec le vrai slug de structure et un compteur mensuel par structure (repart à 1 à chaque changement de mois). Rafraîchit l'index Elasticsearch.
- **`show(Prescription)`** *(route `app_gestapp_prescription_show`)* — Fiche détail. Non référencée dans l'UI, voir Observations.
- **`edit(Request, Prescription, EntityManagerInterface, Index)`** — Cœur de la machine à états : fait avancer la prescription selon l'étape courante et le rôle du soumetteur. Marque aussi l'équipement choisi comme indisponible. Rafraîchit l'index Elasticsearch.
- **`closedcase(Prescription, EntityManagerInterface, Request)`** *(route `app_gestapp_prescription_closedcase`)* — Clôture la prescription (étape `ValidCase`), renseigne commune/code postal depuis le lieu de médiation. Non référencée dans l'UI ni son template, voir Observations.
- **`delete(Request, Prescription, EntityManagerInterface, Index)`** — Suppression classique (formulaire, redirection), sans nettoyage des fichiers ni de Docuseal.
- **`del(Request, Prescription, EntityManagerInterface, PrescriptionRepository)`** — Suppression complète appelée en AJAX, protégée par vérification CSRF en tête de méthode : supprime aussi les fichiers PDF sur disque et archive la soumission Docuseal liée. Retourne la liste HTML mise à jour en JSON. Voir la note Elasticsearch/bénéficiaire déjà résolue dans [FIXES.md](../../FIXES.md) et [elastica-listener.md](../elastica-listener.md) (postRemove listener côté `Prescription`).

## Code mort traité

- **Import inutilisé supprimé** : `use App\Repository\MemberRepository;` — jamais référencé dans le fichier.
- **Trois méthodes entièrement mortes supprimées** : `getPrescriptionsForPrescriptor()`, `getPrescriptionsForMediator()`, `getPrescriptionsForAdmin()` — sans route, sans appel interne, sans référence externe. Contrairement aux autres cas signalés dans cet audit (routes non liées mais atteignables par URL directe), celles-ci étaient strictement inatteignables.
- **Variable inutilisée supprimée** dans `edit()` : `$competence = $prescription->getCompetence();` (appel d'un getter sans effet de bord, jamais réutilisé).
- **Appel de debug commenté supprimé** : `//dd($boolQuery);` dans `index()`.
- **Bloc de code commenté supprimé** dans `closedcase()` : un `return $this->json([...])` entier laissé en commentaire après le vrai `return`, donc doublement mort (commenté + inatteignable).

## Correction de sécurité

- **CSRF ajouté sur `del()`** : la route réellement utilisée par les boutons de suppression de l'UI n'avait aucune vérification (contrairement à `delete()`, non utilisée, qui en avait une). `del()` vérifie désormais `isCsrfTokenValid('delete'.$prescription->getId(), ...)` en tête de méthode et répond `{code: 422, message: '...'}` (HTTP 200) si le jeton est invalide. Câblage bout en bout : le jeton est généré dans les deux templates qui déclenchent la modale (`admin/dashboard/include/_liste.html.twig`, `gestapp/prescription/include/_liste.html.twig`, attribut `data-csrf`), transmis par les deux scripts qui gèrent cette modale (`assets/js/pages/admin/dashboard/index.js`, `assets/js/pages/gestapp/prescription/index_prescription.js`) via `URLSearchParams`.
- **Bug corrigé au passage** : la réponse d'erreur utilisait un vrai statut HTTP 400/422, ce qui fait qu'axios bascule sur `.catch()` (vide dans ce code) au lieu de `.then()` — l'utilisateur ne voyait donc jamais le message d'erreur. Toutes les réponses JSON "métier" de cette route utilisent maintenant HTTP 200, avec le code d'erreur porté par le champ `code` du corps JSON (convention déjà utilisée ailleurs dans le contrôleur, ex. `prescriptionShowQrcode`).

## Observations

- **`await()`, `show()`, `closedcase()` probablement mortes** : aucune vue ni script du projet ne référence `app_gestapp_prescription_await`, `app_gestapp_prescription_show`, ni `app_gestapp_prescription_closedcase` (recherche exhaustive dans `templates/` et `assets/js/`). Pour `closedcase()`, même son propre template `_formClosedCase.html.twig` n'est inclus nulle part ailleurs — la fonctionnalité de "clôture de dossier" semble non finalisée ou abandonnée. Non supprimées par prudence.
- **Duplication de logique métier** entre `index()` (recherche) et `await()`/`listPrescriptionForAdmin()` (Doctrine classique) : trois façons de lister des prescriptions filtrées par rôle, avec des critères légèrement différents à chaque fois. Si `await()` est confirmée morte, il reste `index()` (Elasticsearch) et `listPrescriptionForAdmin()` (Doctrine, spécifique aux signées) — ce qui reste cohérent.
