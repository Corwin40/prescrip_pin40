# HtmlToPdfController — Génération PDF (Gotenberg)

`src/Controller/Gestapp/HtmlToPdfController.php`

## Rôle

Convertit le template de prescription en PDF via **Gotenberg** (service externe de conversion HTML→PDF). Préfixe `/admin/generatepdf`. Dépendances : `GotenbergPdfInterface`, `EntityManagerInterface`.

## Routes

| Méthode HTTP | Chemin | Nom de route | Action | Utilisée depuis |
|---|---|---|---|---|
| GET (implicite) | `/admin/generatepdf/prescription/{id}` | `app_generate_prescription_pdf` | Génère le PDF original | `templates/gestapp/prescription/include/badge.html.twig` |
| GET (implicite) | `/admin/generatepdf/regenerate_prescription/{id}` | `app_regenerate_prescription_pdf` | Régénère le PDF sans changer l'étape | **Aucune** — voir Observations |
| GET (implicite) | `/admin/generatepdf/prescription/preview/{id}` | `app_prescription_preview` | Aperçu HTML du template | **Aucune** — voir Observations |

## Méthodes

- **`generatePrescriptionPdf(Prescription, GotenbergPdfInterface, EntityManagerInterface)`** — Rend `gestapp/htmltopdf/prescriptionpdf.html.twig` en PDF via Gotenberg, écrit le fichier sur disque (`prescription_original_directory`), enregistre le chemin sur la prescription et la fait passer à l'étape `GeneratePDF`.
- **`regeneratePrescriptionPdf(Prescription, GotenbergPdfInterface, EntityManagerInterface)`** — Logique quasi identique à `generatePrescriptionPdf()`, à la différence près qu'elle ne modifie pas l'étape de la prescription. Non appelée nulle part dans l'application.
- **`preview(Prescription)`** — Rend le même template en HTML brut (sans passer par Gotenberg), pour aperçu. Non appelée nulle part dans l'application.

## Code mort traité

- **Imports inutilisés supprimés** : `use Docuseal\Api;` et `use Docuseal\Docuseal;` — aucune des deux classes n'était référencée dans le fichier (la génération de PDF n'a pas de dépendance directe à Docuseal, malgré ces imports).

## Observations

- **`regeneratePrescriptionPdf()` et `preview()` probablement mortes** : aucune vue ni script JS du projet ne référence `app_regenerate_prescription_pdf` ni `app_prescription_preview` (recherche exhaustive dans `templates/`, `assets/js/` et `src/`). Non supprimées par prudence.
- **Duplication de logique** entre `generatePrescriptionPdf()` et `regeneratePrescriptionPdf()` : le corps des deux méthodes est identique à l'exception de la ligne `$prescription->setStep(...)`. Si `regeneratePrescriptionPdf()` s'avère effectivement inutilisée et supprimée, ce n'est plus un souci ; sinon, factoriser la partie commune limiterait le risque de divergence future.
