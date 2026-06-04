# CP5 — Mettre en place une base de données relationnelle

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP5 du titre professionnel DWWM couvre la capacité à concevoir et mettre en place une base de données relationnelle adaptée aux besoins d'une application web. Elle implique :

- La modélisation du schéma relationnel (entités, attributs, relations)
- La création et la gestion de la base de données (DDL)
- L'utilisation d'un ORM (Object-Relational Mapping) pour le mapping objet-base
- Le versioning du schéma via des migrations
- La sécurisation des accès à la base de données

---

## 2. Contexte du projet

L'application **prescrip_pin40** gère des prescriptions numériques dans le cadre de l'inclusion numérique. Le modèle de données doit refléter les acteurs du dispositif :

- Des **structures** (prescriptrices ou lieux de médiation)
- Des **membres** (utilisateurs de l'application, rattachés à une structure)
- Des **bénéficiaires** (personnes accompagnées)
- Des **prescriptions** (dossiers de prescription d'équipements numériques)
- Des **équipements** (matériel informatique à attribuer)
- Des **compétences** numériques évaluées
- Des **documents** attachés aux prescriptions
- Des entités de service (**Docuseal** pour la signature électronique)

La base de données choisie est **MariaDB 10.11** (compatible MySQL), gérée via **Doctrine ORM 3** avec le **Doctrine Migrations Bundle**.

---

## 3. Schéma relationnel

### 3.1 Diagramme des entités et relations

```
┌─────────────────┐          ┌─────────────────────┐
│    Structure     │          │       Member         │
│─────────────────│          │─────────────────────│
│ id (PK)         │◄────────►│ id (PK)              │
│ name            │  ManyToOne│ email (UNIQUE)       │
│ slug            │          │ password (hashed)    │
│ address         │          │ roles (JSON)         │
│ zipcode         │          │ civility (enum)      │
│ city            │          │ firstname            │
│ contactEmail    │          │ lastname             │
│ contactPhone    │          │ slug                 │
│ createdAt       │          │ isVerified           │
│ updatedAt       │          │ isRespStructure      │
│ countBeneficiary│          │ structure_id (FK)    │
│ countPrescription│         │ referent_id (FK→self)│
└────────┬────────┘          │ createdAt            │
         │                   │ updatedAt            │
         │ OneToMany         └─────────┬───────────┘
         │                             │ OneToMany
         ▼                             ▼
┌─────────────────┐          ┌─────────────────────┐
│   Beneficiary   │          │     Beneficiary     │
│─────────────────│          │    (référent FK)    │
│ id (PK)         │          └─────────────────────┘
│ firstname       │
│ lastname        │
│ civility (enum) │
│ gender          │
│ ageGroup        │
│ professionnalStatus│
│ structure_id (FK)│
│ referent_id (FK)│
│ createdAt       │
│ updatedAt       │
└────────┬────────┘
         │ OneToOne
         ▼
┌─────────────────────────────────────────────────┐
│                  Prescription                    │
│─────────────────────────────────────────────────│
│ id (PK)                                          │
│ ref (VARCHAR 100)                                │
│ objectName                                       │
│ details (TEXT)                                   │
│ baseCompetence                                   │
│ compteur (SMALLINT)                              │
│ commune                                          │
│ cp (VARCHAR 5)                                   │
│ validcase (BOOL)                                 │
│ isOpenByPrescriptor (BOOL)                       │
│ isOpenByMediator (BOOL)                          │
│ step (ENUM: StepPrescription)                    │
│ status (ENUM: StatusPrescription)                │
│ path (chemin PDF original)                       │
│ pathSigned (chemin PDF signé)                    │
│ pathSignedCertif (chemin certificat)             │
│ closedAt (DATE)                                  │
│ createdAt (DATE)                                 │
│ updatedAt (DATE)                                 │
│ prescriptor_id (FK→Structure)                    │
│ lieuMediation_id (FK→Structure)                  │
│ beneficiaire_id (FK→Beneficiary, UNIQUE)         │
│ equipement_id (FK→Equipment, UNIQUE)             │
│ competence_id (FK→Competence, UNIQUE)            │
└──────────┬──────────────────────────────────────┘
           │
    ┌──────┴──────┬──────────────┬──────────────┐
    ▼             ▼              ▼              ▼
┌─────────┐ ┌─────────┐ ┌──────────┐ ┌──────────┐
│Equipment│ │Competence│ │ Document │ │ Docuseal │
│─────────│ │─────────│ │──────────│ │──────────│
│id (PK)  │ │id (PK)  │ │id (PK)   │ │id (PK)   │
│...      │ │...      │ │filename  │ │idSeal    │
└─────────┘ └─────────┘ │path      │ │...       │
                         │prescription_id (FK)│ └──────────┘
                         └──────────┘
```

### 3.2 Tableau des relations

| Entité source | Relation | Entité cible | Cardinalité | Cascade |
|---------------|----------|--------------|-------------|---------|
| Structure | OneToMany | Member | 1→N | — |
| Structure | OneToMany | Beneficiary | 1→N | persist |
| Structure | OneToMany | Prescription (prescriptor) | 1→N | — |
| Structure | OneToMany | Prescription (lieuMediation) | 1→N | — |
| Member | ManyToOne | Structure | N→1 | — |
| Member | ManyToOne | Member (referent) | N→1 (self) | — |
| Member | OneToMany | Beneficiary | 1→N | — |
| Beneficiary | OneToOne | Prescription | 1→1 | persist |
| Prescription | ManyToOne | Structure (prescriptor) | N→1 | — |
| Prescription | ManyToOne | Structure (lieuMediation) | N→1 | — |
| Prescription | OneToOne | Beneficiary | 1→1 | persist |
| Prescription | OneToOne | Equipment | 1→1 | persist, remove |
| Prescription | OneToOne | Competence | 1→1 | persist, remove |
| Prescription | OneToMany | Document | 1→N | — |
| Prescription | OneToOne | Docuseal | 1→1 | persist, remove |

---

## 4. Mapping Doctrine ORM

### 4.1 Entité Prescription (entité centrale)

```php
<?php
// src/Entity/Gestapp/Prescription.php

namespace App\Entity\Gestapp;

use App\Config\StatusPrescription;
use App\Config\StepPrescription;
use App\Entity\Admin\Structure;
use App\Repository\Gestapp\PrescriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrescriptionRepository::class)]
#[ORM\HasLifecycleCallbacks]  // Active les callbacks PrePersist/PreUpdate
class Prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relation vers Structure (prescripteur) : N prescriptions → 1 structure
    #[ORM\ManyToOne(inversedBy: 'prescriptions')]
    private ?Structure $prescriptor = null;

    // Relation vers Structure (médiateur) : N prescriptions → 1 structure
    #[ORM\ManyToOne(inversedBy: 'lieuxmediation')]
    private ?Structure $lieuMediation = null;

    // Relation 1→1 : une prescription = un bénéficiaire
    #[ORM\OneToOne(inversedBy: 'prescription', cascade: ['persist'])]
    private ?Beneficiary $beneficiaire = null;

    // Relation 1→1 avec cascade remove : supprime l'équipement avec la prescription
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Equipment $equipement = null;

    // Enum PHP 8.1 : stocké comme string en BDD, converti automatiquement
    #[ORM\Column(enumType: StepPrescription::class)]
    private ?StepPrescription $step = null;

    #[ORM\Column(enumType: StatusPrescription::class)]
    private ?StatusPrescription $status = null;

    // Collection 1→N : une prescription a plusieurs documents
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'prescription')]
    private Collection $documents;

    // Lifecycle callback : définit createdAt automatiquement à la création
    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime('now');
        return $this;
    }

    // Lifecycle callback : met à jour updatedAt à chaque modification
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime('now');
        return $this;
    }
}
```

### 4.2 Entité Member (utilisateur Symfony)

```php
<?php
// src/Entity/Admin/Member.php

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: '`member`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    // Stocké en JSON : ['ROLE_PRESCRIPTEUR'] → converti en array PHP
    #[ORM\Column]
    private array $roles = [];

    // Mot de passe haché (bcrypt via Symfony Security)
    #[ORM\Column]
    private ?string $password = null;

    // Auto-référence : un membre peut avoir un référent (autre membre)
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'members')]
    private ?self $referent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'referent')]
    private Collection $members;

    // Enum pour la civilité (M./Mme)
    #[ORM\Column(enumType: Civility::class)]
    private ?Civility $civility = null;

    // Sérialisation personnalisée : hash le mot de passe pour éviter les fuites
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        return $data;
    }
}
```

### 4.3 Enums PHP 8.1

```php
<?php
// src/Config/StepPrescription.php

namespace App\Config;

enum StepPrescription: string
{
    case Open = 'Open';                          // Dossier ouvert par admin
    case OneParts = 'OneParts';                  // Une partie remplie
    case TwoParts = 'TwoParts';                  // Deux parties remplies
    case ChoiceEquipment = 'ChoiceEquipment';    // Choix de l'équipement
    case ValidCase = 'ValidCase';                // Dossier validé
    case GeneratePDF = 'GeneratePDF';            // PDF généré
    case SubmissionForSigned = 'SubmissionForSigned'; // Envoyé à signature
    case Signed = 'Signed';                      // Signé électroniquement
    case Closed = 'Closed';                      // Dossier clôturé
    case Upload = 'uploadByAlpi';                // Uploadé par ALPI
}

// src/Config/StatusPrescription.php
enum StatusPrescription: string
{
    case OpenByAdministrator = 'Dossier ouvert par un administrateur';
    case OpenByMediator = 'Dossier ouvert par un médiateur';
    case OpenByPrescriptor = 'Dossier ouvert par un prescripteur';
    case finished = 'Dossier validé';
}
```

L'avantage des enums PHP 8.1 avec Doctrine : le type est stocké sous forme de `VARCHAR` en base de données, mais Doctrine le convertit automatiquement en objet PHP lors de la lecture. Cela garantit la cohérence des valeurs sans requête de validation supplémentaire.

---

## 5. Migrations Doctrine

### 5.1 Principe

Les migrations permettent de versionner les modifications du schéma de base de données. Chaque migration est un fichier PHP horodaté qui contient les requêtes SQL `up()` (appliquer) et `down()` (annuler).

```
migrations/
├── Version20260430062931.php   # Création du schéma initial
├── Version20260504091213.php   # Ajout colonnes beneficiary
├── Version20260528145844.php   # Ajout docuseal
├── Version20260529060845.php   # Modification prescription
├── Version20260530150453.php   # Ajout pathSigned/pathSignedCertif
├── Version20260531101854.php   # Ajout closedAt
├── Version20260531103023.php   # Ajout countBeneficiary/countPrescription
└── Version20260531113505.php   # Modification statuts
```

### 5.2 Exemple de migration

```php
<?php
// migrations/Version20260531101854.php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260531101854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout champ closedAt sur la prescription';
    }

    public function up(Schema $schema): void
    {
        // Ajout colonne closedAt (nullable, type DATE)
        $this->addSql(<<<'SQL'
            ALTER TABLE prescription
            ADD closed_at DATE DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Rollback : suppression de la colonne
        $this->addSql(<<<'SQL'
            ALTER TABLE prescription
            DROP COLUMN closed_at
        SQL);
    }
}
```

### 5.3 Commandes Doctrine

```bash
# Générer une migration depuis les entités PHP
php bin/console doctrine:migrations:diff

# Appliquer toutes les migrations en attente
php bin/console doctrine:migrations:migrate --no-interaction

# Voir le statut des migrations
php bin/console doctrine:migrations:status

# Valider le mapping ORM (cohérence entités <> schéma BDD)
php bin/console doctrine:schema:validate
```

---

## 6. Configuration Doctrine

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
        # MariaDB - désactive la gestion des enums natifs
        # pour utiliser les PHP Enums à la place
        types:
            step_prescription:
                class: App\DBAL\Types\StepPrescriptionType

    orm:
        auto_generate_proxy_classes: true
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        auto_mapping: true
        mappings:
            App:
                is_bundle: false
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
                alias: App
```

---

## 7. SQL représentatif généré

Voici le SQL équivalent aux entités principales du projet (tel que généré par `doctrine:schema:create`) :

```sql
-- Table member (utilisateurs)
CREATE TABLE `member` (
    id INT AUTO_INCREMENT NOT NULL,
    structure_id INT DEFAULT NULL,
    referent_id INT DEFAULT NULL,
    email VARCHAR(180) NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    civility VARCHAR(10) NOT NULL,  -- stocke la valeur de l'enum Civility
    firstname VARCHAR(255) DEFAULT NULL,
    lastname VARCHAR(255) DEFAULT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    is_resp_structure TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
    INDEX IDX_73DB9CF3 (structure_id),
    INDEX IDX_35C246D5 (referent_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Table structure (structures prescriptrices et lieux de médiation)
CREATE TABLE structure (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    zipcode VARCHAR(5) DEFAULT NULL,
    city VARCHAR(255) DEFAULT NULL,
    contact_email VARCHAR(255) DEFAULT NULL,
    contact_phone VARCHAR(14) DEFAULT NULL,
    contact_responsable_civility VARCHAR(10) DEFAULT NULL,
    contact_responsable_firstname VARCHAR(100) DEFAULT NULL,
    contact_responsable_lastname VARCHAR(100) DEFAULT NULL,
    count_beneficiary INT DEFAULT 0,
    count_prescription INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Table prescription (entité centrale)
CREATE TABLE prescription (
    id INT AUTO_INCREMENT NOT NULL,
    prescriptor_id INT DEFAULT NULL,
    lieu_mediation_id INT DEFAULT NULL,
    beneficiaire_id INT DEFAULT NULL,
    equipement_id INT DEFAULT NULL,
    competence_id INT DEFAULT NULL,
    ref VARCHAR(100) NOT NULL,
    object_name VARCHAR(255) DEFAULT NULL,
    details LONGTEXT DEFAULT NULL,
    base_competence VARCHAR(100) NOT NULL,
    compteur SMALLINT NOT NULL,
    commune VARCHAR(100) DEFAULT NULL,
    cp VARCHAR(5) DEFAULT NULL,
    validcase TINYINT(1) NOT NULL DEFAULT 0,
    is_open_by_prescriptor TINYINT(1) NOT NULL DEFAULT 0,
    is_open_by_mediator TINYINT(1) DEFAULT NULL,
    step VARCHAR(50) NOT NULL,    -- valeur de l'enum StepPrescription
    status VARCHAR(100) NOT NULL, -- valeur de l'enum StatusPrescription
    path VARCHAR(255) DEFAULT NULL,
    path_signed VARCHAR(255) DEFAULT NULL,
    path_signed_certif VARCHAR(255) DEFAULT NULL,
    closed_at DATE DEFAULT NULL,
    created_at DATE NOT NULL,
    updated_at DATE NOT NULL,
    UNIQUE INDEX UNIQ_beneficiaire (beneficiaire_id),
    UNIQUE INDEX UNIQ_equipement (equipement_id),
    UNIQUE INDEX UNIQ_competence (competence_id),
    INDEX IDX_prescriptor (prescriptor_id),
    INDEX IDX_lieu_mediation (lieu_mediation_id),
    FOREIGN KEY (prescriptor_id) REFERENCES structure(id),
    FOREIGN KEY (lieu_mediation_id) REFERENCES structure(id),
    FOREIGN KEY (beneficiaire_id) REFERENCES beneficiary(id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 8. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| Le schéma de la base de données est conforme aux besoins identifiés | 8 entités couvrant tous les acteurs et objets métier du dispositif |
| Les bonnes pratiques de conception sont respectées | Normalisation (3NF), clés étrangères, index, unicité |
| Les relations entre les entités sont correctement définies | 15 relations (ManyToOne, OneToMany, OneToOne) avec cascades appropriées |
| Les migrations permettent de versionner l'évolution du schéma | 8 migrations horodatées depuis avril 2026 |
| La sécurité des accès à la BDD est assurée | Utilisateur MariaDB dédié, mot de passe dans .env.local |
| Les enums garantissent la cohérence des données | PHP 8.1 backed enums pour StepPrescription (10 valeurs) et StatusPrescription (4 valeurs) |
| Les timestamps sont gérés automatiquement | Lifecycle callbacks PrePersist/PreUpdate sur toutes les entités |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*