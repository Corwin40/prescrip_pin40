# CP7 — Développer des composants métier côté serveur

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP7 du titre professionnel DWWM couvre la capacité à développer la logique métier côté serveur d'une application web. Elle implique :

- La mise en œuvre d'une architecture MVC (Model-View-Controller)
- Le développement de la logique métier (traitements, règles de gestion)
- La sécurisation de l'application (authentification, autorisation, validation)
- L'intégration de services tiers (API, bibliothèques)
- La gestion des événements applicatifs

---

## 2. Contexte du projet

**prescrip_pin40** implémente un workflow métier complexe de gestion de prescriptions numériques. Les composants métier couvrent :

1. **Machine à états** : le cycle de vie d'une prescription (10 états)
2. **Contrôle d'accès** : 5 rôles utilisateurs avec héritage
3. **Génération de PDF** : via le service Gotenberg
4. **Signature électronique** : via l'API Docuseal
5. **Génération de QR Code** : via la bibliothèque chillerlan/php-qrcode
6. **Gestion des sessions** : timeout automatique via EventListener
7. **Formulaires adaptatifs** : logique de validation selon le rôle

---

## 3. Machine à états — Workflow des prescriptions

### 3.1 Les états (enum StepPrescription)

```php
<?php
// src/Config/StepPrescription.php

namespace App\Config;

enum StepPrescription: string
{
    case Open = 'Open';                               // 1. Dossier créé par admin
    case OneParts = 'OneParts';                       // 2. Une partie remplie (prescripteur OU médiateur)
    case TwoParts = 'TwoParts';                       // 3. Deux parties remplies (prescripteur ET médiateur)
    case ChoiceEquipment = 'ChoiceEquipment';          // 4. Équipement numérique choisi
    case ValidCase = 'ValidCase';                     // 5. Dossier validé
    case GeneratePDF = 'GeneratePDF';                 // 6. PDF généré
    case SubmissionForSigned = 'SubmissionForSigned'; // 7. Soumis à signature Docuseal
    case Signed = 'Signed';                           // 8. Signé électroniquement
    case Closed = 'Closed';                           // 9. Dossier clôturé
    case Upload = 'uploadByAlpi';                     // 10. Uploadé par ALPI
}

// Les statuts décrivent qui a ouvert le dossier
enum StatusPrescription: string
{
    case OpenByAdministrator = 'Dossier ouvert par un administrateur';
    case OpenByMediator = 'Dossier ouvert par un médiateur';
    case OpenByPrescriptor = 'Dossier ouvert par un prescripteur';
    case finished = 'Dossier validé';
}
```

### 3.2 Diagramme de transitions

```
[CRÉATION]
     │
     ├─── par Admin ──────► Open
     ├─── par Médiateur ──► OneParts
     └─── par Prescripteur► OneParts
                              │
                         ┌────┴────┐
                         │         │
                    Médiateur  Prescripteur
                    complète   complète
                         │         │
                         └────┬────┘
                              ▼
                           TwoParts
                              │
                         Médiateur/Admin
                         choisit équipement
                              │
                              ▼
                       ChoiceEquipment
                              │
                         Validation
                              │
                              ▼
                          ValidCase
                              │
                         Génération PDF
                         (Gotenberg)
                              │
                              ▼
                         GeneratePDF
                              │
                         Envoi Docuseal
                              │
                              ▼
                      SubmissionForSigned
                              │
                         Signature reçue
                              │
                              ▼
                            Signed
                              │
                           Clôture
                              │
                              ▼
                            Closed
```

### 3.3 Implémentation des transitions dans le Controller

```php
<?php
// src/Controller/Gestapp/PrescriptionController.php (extrait)

public function edit(
    Request $request,
    Prescription $prescription,
    EntityManagerInterface $entityManager
): Response {
    $user = $this->getUser();
    $step = $prescription->getStep();

    if ($form->isSubmitted() && $form->isValid()) {

        // TRANSITION : Open → ChoiceEquipment (admin uniquement)
        if ($step == StepPrescription::Open) {
            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())
                || in_array('ROLE_ADMIN', $user->getRoles())) {
                $beneficiaire = $form->get('beneficiaire')->getData();
                $prescription->setPrescriptor($beneficiaire->getStructure());
                $prescription->setStep(StepPrescription::ChoiceEquipment);
            }
        }

        // TRANSITION : OneParts → TwoParts
        if ($step == StepPrescription::OneParts) {
            if (in_array('ROLE_MEDIATEUR', $user->getRoles())) {
                $prescription->setIsOpenByMediator(1);
                $prescription->setStep(StepPrescription::TwoParts);
            }
            if (in_array('ROLE_PRESCRIPTEUR', $user->getRoles())) {
                $prescription->setIsOpenByPrescriptor(1);
                $prescription->setStep(StepPrescription::TwoParts);
            }
        }

        // TRANSITION : TwoParts → ChoiceEquipment
        elseif ($step == StepPrescription::TwoParts) {
            if (in_array('ROLE_MEDIATEUR', $user->getRoles())) {
                $prescription->setIsOpenByMediator(1);
                $prescription->setStep(StepPrescription::ChoiceEquipment);
            }
        }

        // TRANSITION : ChoiceEquipment → ValidCase
        elseif ($step == StepPrescription::ChoiceEquipment) {
            $prescription->setValidcase(1);
            $prescription->setStep(StepPrescription::ValidCase);

            // L'équipement choisi est marqué indisponible
            $equipment = $form->get('equipement')->getData();
            if ($equipment) {
                $prescription->setEquipement($equipment);
                $equipment->setIsDispo(0);
            }
        }

        $entityManager->flush();
        return $this->redirectToRoute('app_gestapp_prescription_index');
    }
}
```

---

## 4. Sécurité applicative

### 4.1 Configuration Symfony Security

```yaml
# config/packages/security.yaml
security:
    # Hachage bcrypt automatique des mots de passe
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
        app_user_provider:
            entity:
                class: App\Entity\Admin\Member
                property: email  # L'email comme identifiant unique

    firewalls:
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: app_login
                check_path: app_login
                enable_csrf: true  # Protection CSRF sur le formulaire de login
                default_target_path: app_admin_dashboard_index
            logout:
                path: app_logout

    # Hiérarchie des rôles : SUPER_ADMIN hérite de tout
    role_hierarchy:
        ROLE_PRESCRIPTEUR: ROLE_USER
        ROLE_MEDIATEUR: ROLE_PRESCRIPTEUR
        ROLE_CONDITIONNEUR: ROLE_USER
        ROLE_ADMIN: [ROLE_MEDIATEUR, ROLE_PRESCRIPTEUR, ROLE_CONDITIONNEUR]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    # Contrôle d'accès par URL
    access_control:
        - { path: ^/admin/login, roles: PUBLIC_ACCESS }
        - { path: ^/admin/dashboard, roles: [ROLE_ADMIN, ROLE_MEDIATEUR, ROLE_PRESCRIPTEUR] }
        - { path: ^/admin/generatepdf, roles: [ROLE_ADMIN, ROLE_MEDIATEUR, ROLE_PRESCRIPTEUR] }
        - { path: ^/admin/member, roles: [ROLE_ADMIN, ROLE_MEDIATEUR] }
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/, roles: PUBLIC_ACCESS }
```

### 4.2 Protection CSRF dans les formulaires

```php
// Vérification CSRF côté serveur pour toutes les suppressions
#[Route('/{id}', name: 'app_gestapp_prescription_delete', methods: ['POST'])]
public function delete(
    Request $request,
    Prescription $prescription,
    EntityManagerInterface $entityManager
): Response {
    if ($this->isCsrfTokenValid('delete'.$prescription->getId(), $request->getPayload()->getString('_token'))) {
        $entityManager->remove($prescription);
        $entityManager->flush();
    }
    return $this->redirectToRoute('app_gestapp_prescription_index');
}
```

---

## 5. Génération de PDF — Gotenberg

### 5.1 Service Gotenberg

Gotenberg est un service Docker qui expose une API REST pour convertir du HTML en PDF. Il est intégré via le bundle `sensiolabs/gotenberg-bundle`.

```php
<?php
// src/Controller/Gestapp/HtmlToPdfController.php

namespace App\Controller\Gestapp;

use App\Config\StepPrescription;
use App\Entity\Gestapp\Prescription;
use Sensiolabs\GotenbergBundle\GotenbergPdfInterface;
use Sensiolabs\GotenbergBundle\Processor\FileProcessor;
use Symfony\Component\Filesystem\Filesystem;

#[Route("/admin/generatepdf")]
final class HtmlToPdfController extends AbstractController
{
    #[Route('/prescription/{id}', name: 'app_generate_prescription_pdf')]
    public function generatePrescriptionPdf(
        Prescription $prescription,
        GotenbergPdfInterface $gotenberg,
        EntityManagerInterface $em
    ): Response {
        $filenameNoExt = $prescription->getRef().'_original';
        $slugStructure = $prescription->getPrescriptor()->getSlug();

        // Chemin de stockage : /public/prescriptions/{slug-structure}/
        $dir = $this->getParameter('prescription_original_directory').$slugStructure;
        $path_url = $this->getParameter('prescription_original_directory_url')
            .$slugStructure.'/'.$filenameNoExt.'.pdf';

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Génération via Gotenberg : rendu du template Twig → PDF
        $gotenberg
            ->html()
            ->content('gestapp/htmltopdf/prescriptionpdf.html.twig', [
                'prescription' => $prescription,
                'pdf' => true,
            ])
            ->fileName($filenameNoExt)
            ->processor(new FileProcessor(new Filesystem(), $dir))  // Sauvegarde sur disque
            ->generate()
            ->process()
        ;

        // Mise à jour de la prescription : chemin PDF + avancement étape
        $prescription->setPath($path_url);
        $prescription->setStep(StepPrescription::GeneratePDF);
        $em->flush();

        return $this->redirectToRoute('app_gestapp_prescription_index');
    }

    // Prévisualisation sans sauvegarde
    #[Route('/prescription/preview/{id}', name: 'app_prescription_preview')]
    public function preview(Prescription $prescription): Response
    {
        return $this->render('gestapp/htmltopdf/prescriptionpdf.html.twig', [
            'prescription' => $prescription,
        ]);
    }
}
```

**Flux de génération PDF :**
```
Controller → Twig template (prescriptionpdf.html.twig)
           → Gotenberg API (HTTP POST)
           → PDF binaire
           → FileProcessor (sauvegarde dans /public/prescriptions/{structure}/)
           → Update BDD (path + step = GeneratePDF)
```

---

## 6. Génération de QR Code

```php
<?php
// src/Service/QrcodeGenerator.php

namespace App\Service;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrcodeGenerator
{
    public function generate(?string $data): mixed
    {
        if (!$data) {
            return null;
        }

        $options = new QROptions([
            'version' => 3,  // Détermine la taille du QR Code
        ]);

        // Retourne une image PNG encodée en base64
        return (new QRCode($options))->render($data);
    }
}
```

Le QR Code est généré à partir de l'URL de la prescription pour permettre un accès rapide via mobile. Il est intégré dans le template PDF généré par Gotenberg.

---

## 7. EventListeners — Composants événementiels

### 7.1 SessionTimeoutListener

```php
<?php
// src/EventListener/Listener/SessionTimeoutListener.php

namespace App\EventListener\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SessionTimeoutListener
{
    private const SESSION_TIMEOUT = 1800; // 30 minutes

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private RouterInterface $router
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        // Déconnexion si le token de session a expiré
        if ($this->tokenStorage->getToken() !== null) {
            $lastActivity = $session->get('lastActivity', time());

            if (time() - $lastActivity > self::SESSION_TIMEOUT) {
                $this->tokenStorage->setToken(null);
                $session->invalidate();

                $event->setResponse(
                    new RedirectResponse($this->router->generate('app_login'))
                );
                return;
            }
        }

        $session->set('lastActivity', time());
    }
}
```

### 7.2 PrescriptionElasticaListener

Ce listener maintient la cohérence entre la BDD SQL et l'index Elasticsearch lors de la suppression d'une prescription (voir CP6 pour le détail).

```php
<?php
// src/EventListener/PrescriptionElasticaListener.php

class PrescriptionElasticaListener
{
    public function __construct(
        private ObjectPersisterInterface $beneficiaryPersister
    ) {}

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Prescription) { return; }

        // Réindexe le bénéficiaire dans Elasticsearch après suppression de la prescription
        $beneficiary = $entity->getBeneficiaire();
        if ($beneficiary) {
            $this->beneficiaryPersister->replaceOne($beneficiary);
        }
    }
}
```

---

## 8. Vérification d'email à l'inscription

```php
<?php
// src/Security/EmailVerifier.php (extrait)

namespace App\Security;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager
    ) {}

    public function sendEmailConfirmation(
        string $verifyEmailRouteName,
        UserInterface $user,
        TemplatedEmail $email
    ): void {
        // Génère un lien signé de vérification (expire après 1h)
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            $user->getEmail()
        );

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();

        $email->context($context);
        $this->mailer->send($email);
    }

    public function handleEmailConfirmation(
        Request $request,
        UserInterface $user
    ): void {
        $this->verifyEmailHelper->validateEmailConfirmation(
            $request->getUri(),
            (string) $user->getId(),
            $user->getEmail()
        );

        $user->setIsVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
```

---

## 9. Lifecycle callbacks sur les entités

Toutes les entités utilisent les callbacks Doctrine pour gérer automatiquement les timestamps :

```php
// Pattern utilisé sur Member, Structure, Beneficiary, Prescription

#[ORM\HasLifecycleCallbacks]
class Prescription
{
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    #[ORM\PrePersist]  // Déclenché une seule fois, à la création
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime('now');
        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]   // Déclenché à chaque sauvegarde
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime('now');
        return $this;
    }
}
```

---

## 10. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| La logique métier est implémentée côté serveur | Machine à états avec 10 étapes, transitions conditionnelles par rôle |
| La sécurité de l'application est assurée | Symfony Security (rôles, hiérarchie, access_control, CSRF) |
| Des services applicatifs sont développés | QrcodeGenerator, EmailVerifier, HtmlToPdfController (Gotenberg) |
| Des API tierces sont intégrées | Gotenberg (PDF), Docuseal (signature), chillerlan/qrcode |
| La gestion des événements est mise en œuvre | SessionTimeoutListener, PrescriptionElasticaListener |
| Les données sont validées côté serveur | Symfony Validator sur toutes les entités, CSRF sur tous les formulaires |
| L'architecture MVC est respectée | Controllers fins, logique dans les Services, entités sans logique UI |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*