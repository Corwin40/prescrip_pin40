# CP6 — Développer des composants d'accès aux données SQL et NoSQL

**Projet :** prescrip_pin40  
**Titre professionnel :** Développeur Web et Web Mobile (DWWM)  
**Référentiel :** REAC DWWM — Ministère du Travail, de l'Emploi et de l'Insertion

---

## 1. Description de la compétence

La CP6 du titre professionnel DWWM couvre la capacité à développer les couches d'accès aux données d'une application web, qu'il s'agisse de bases de données relationnelles (SQL) ou de systèmes NoSQL. Elle implique :

- L'écriture de requêtes SQL optimisées (via ORM ou DQL)
- L'utilisation d'un Query Builder pour les requêtes dynamiques
- L'intégration d'une solution NoSQL (Elasticsearch) pour la recherche full-text
- La synchronisation entre la base relationnelle et l'index NoSQL
- La gestion des performances d'accès aux données

---

## 2. Contexte du projet

**prescrip_pin40** utilise **deux systèmes de stockage complémentaires** :

| Système | Technologie | Usage |
|---------|-------------|-------|
| SQL | MariaDB 10.11 + Doctrine ORM | Source de vérité, CRUD, relations |
| NoSQL | Elasticsearch 7.17 + FOSElasticaBundle | Recherche full-text, filtres complexes |

Cette double approche est justifiée par des besoins distincts :
- La base SQL assure l'**intégrité référentielle** et la **persistance** des données métier
- Elasticsearch apporte des **capacités de recherche** avancées (recherche par référence, filtre par structure, pagination) que SQL rendrait coûteux avec des LIKE sur tables volumineuses

---

## 3. Composants d'accès SQL — Repositories Doctrine

### 3.1 Repository Prescription

Le repository `PrescriptionRepository` encapsule toutes les requêtes DQL spécifiques au domaine métier des prescriptions.

```php
<?php
// src/Repository/Gestapp/PrescriptionRepository.php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\Prescription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PrescriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prescription::class);
    }

    // Filtre par étape (ex: toutes les prescriptions à l'étape "Signed")
    public function filteredByStep($step)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.step = :step')
            ->setParameter('step', $step);

        return $qb->getQuery()->getResult();
    }

    // Filtre sur plusieurs étapes (requête IN)
    public function filteredByMultiSteps(array $excludedSteps): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.step IN (:steps)')
            ->setParameter('steps', $excludedSteps)
            ->getQuery()
            ->getResult();
    }

    // Filtre inverse : tout SAUF une étape
    public function filteredByWithoutStep($step)
    {
        return $this->createQueryBuilder('p')
            ->where('p.step != :step')
            ->setParameter('step', $step)
            ->getQuery()
            ->getResult();
    }

    // Filtre par étape ET par lieu de médiation (pour les médiateurs)
    public function filteredByWithoutStepForMediator($step, $lieuMediation)
    {
        return $this->createQueryBuilder('p')
            ->where('p.step != :step')
            ->setParameter('step', $step)
            ->andWhere('p.lieuMediation = :lieuMediation')
            ->setParameter('lieuMediation', $lieuMediation)
            ->getQuery()
            ->getResult();
    }

    // Filtre par étape ET par prescripteur (pour les prescripteurs)
    public function filteredByWithoutStepForPrescriptor($step, $prescriptor)
    {
        return $this->createQueryBuilder('p')
            ->where('p.step != :step')
            ->setParameter('step', $step)
            ->andWhere('p.prescriptor = :prescriptor')
            ->setParameter('prescriptor', $prescriptor)
            ->getQuery()
            ->getResult();
    }

    // Exclusion de plusieurs étapes (requête NOT IN)
    public function filteredByWithoutMultiSteps(array $excludedSteps): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.step NOT IN (:steps)')
            ->setParameter('steps', $excludedSteps)
            ->getQuery()
            ->getResult();
    }
}
```

### 3.2 Repository Structure — requêtes avec jointures

Le repository `StructureRepository` illustre des requêtes plus complexes avec jointures multi-niveaux.

```php
<?php
// src/Repository/Admin/StructureRepository.php

class StructureRepository extends ServiceEntityRepository
{
    // Récupère les structures d'un prescripteur (pour afficher ses prescriptions)
    public function findPrescriptorsByPrescriptor($structure): array
    {
        return $this->createQueryBuilder('s')
            ->where('s = :idstructure')
            ->setParameter('idstructure', $structure)
            ->getQuery()
            ->getResult();
    }

    // Récupère les structures prescriptrices liées à un médiateur
    // via une jointure 3 niveaux : structure→membres→référent→structure
    public function findPrescriptorsByMediator($structure): array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT s')
            ->join('s.members', 'm')       // Structure a des membres
            ->join('m.referent', 'r')      // Chaque membre a un référent
            ->join('r.structure', 'st')    // Le référent appartient à une structure
            ->where('st = :idstructure')
            ->setParameter('idstructure', $structure)
            ->getQuery()
            ->getResult();
    }

    // Récupère toutes les structures ayant des membres avec référents (admin)
    public function findPrescriptorsByAdmin(): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.members', 'm')
            ->join('m.referent', 'r')
            ->getQuery()
            ->getResult();
    }
}
```

### 3.3 Utilisation dans les controllers

```php
// Dans PrescriptionController.php
// Utilisation du repository selon le rôle de l'utilisateur
if ($member && in_array('ROLE_PRESCRIPTEUR', $member->getRoles())) {
    $prescriptions = $prescriptionRepository->filteredByWithoutStepForPrescriptor(
        StepPrescription::Signed,
        $user->getStructure()
    );
}

if ($member && in_array('ROLE_MEDIATEUR', $member->getRoles())) {
    $prescriptions = $prescriptionRepository->filteredByWithoutStepForMediator(
        StepPrescription::Signed,
        $user->getStructure()
    );
}

// Accès direct via EntityManager pour les opérations simples
$entityManager->persist($prescription);
$entityManager->flush();

// Suppression avec cascade automatique (Document, Equipment, Competence, Docuseal)
$entityManager->remove($prescription);
$entityManager->flush();
```

---

## 4. Composants d'accès NoSQL — Elasticsearch

### 4.1 Configuration de l'index Elasticsearch

```yaml
# config/packages/fos_elastica.yaml
fos_elastica:
    clients:
        default:
            hosts:
                - '%env(ELASTICSEARCH_URL)%'  # http://elasticsearch:9200

    indexes:
        # Index des bénéficiaires
        beneficiary:
            persistence:
                driver: orm
                model: App\Entity\Gestapp\Beneficiary
                provider: ~
                finder: ~
                listener:
                    enabled: true
                    defer: false   # Mise à jour synchrone (immédiate)
                    insert: true   # Indexation à la création
                    update: true   # Réindexation à la modification
                    delete: true   # Suppression de l'index à la suppression
            properties:
                id:
                    type: integer
                firstName:
                    type: text
                    fields:
                        keyword:          # Champ keyword pour les tris et filtres exacts
                            type: keyword
                lastName:
                    type: text
                    fields:
                        keyword:
                            type: keyword
                structure:
                    type: object
                    properties:
                        id: ~             # Seul l'id est indexé pour les filtres

        # Index des prescriptions
        prescription:
            persistence:
                driver: orm
                model: App\Entity\Gestapp\Prescription
                provider: ~
                finder: ~
                listener:
                    enabled: true
                    defer: false
                    insert: true
                    update: true
                    delete: true
            properties:
                id:
                    type: integer
                ref:
                    type: text            # Recherche full-text sur la référence
                    fields:
                        keyword:
                            type: keyword
                prescriptor:
                    type: object
                    properties:
                        id: ~             # Filtre par prescripteur
```

### 4.2 Requêtes Elasticsearch dans le Controller

```php
<?php
// src/Controller/Gestapp/PrescriptionController.php

use FOS\ElasticaBundle\Finder\PaginatedFinderInterface;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use Elastica\Query\Term;
use Elastica\Query\Terms;

class PrescriptionController extends AbstractController
{
    public function __construct(
        private PaginatedFinderInterface $finder,
        private string $docuseal_Key
    ) {}

    #[Route('/', name: 'app_gestapp_prescription_index', methods: ['GET', 'POST'])]
    public function index(Request $request, StructureRepository $structureRepository): Response
    {
        $member = $this->getUser();
        $structureId = $member->getStructure()?->getId();

        // Requête booléenne Elasticsearch (combinaison de filtres)
        $boolQuery = new BoolQuery();

        // FILTRE AUTOMATIQUE SELON LE RÔLE (ne voit que ses propres prescriptions)
        if (in_array('ROLE_PRESCRIPTEUR', $member->getRoles()) && $structureId) {
            // Terme exact : prescriptions de cette structure uniquement
            $termQuery = new Term();
            $termQuery->setTerm('prescriptor.id', $structureId);
            $boolQuery->addFilter($termQuery);  // addFilter = ne score pas, juste filtre
        }

        if (in_array('ROLE_MEDIATEUR', $member->getRoles())) {
            // Termes : prescriptions de plusieurs structures (celles du médiateur)
            $termsQuery = new Terms('prescriptor.id', $prescriptorIds);
            $boolQuery->addFilter($termsQuery);
        }

        // FILTRE OPTIONNEL DEPUIS LE FORMULAIRE DE RECHERCHE
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Recherche full-text sur la référence
            if (!empty($data['ref'])) {
                $matchQuery = new MatchQuery();
                $matchQuery->setField('ref', $data['ref']);
                $boolQuery->addMust($matchQuery);  // addMust = doit matcher (like AND)
            }

            // Filtre exact sur un prescripteur sélectionné
            if (!empty($data['prescriptor'])) {
                $termQuery = new Term();
                $termQuery->setTerm('prescriptor.id', $data['prescriptor']);
                $boolQuery->addFilter($termQuery);
            }
        }

        // Exécution de la requête
        $query = new Query($boolQuery);
        $query->setSize(50);  // Limite à 50 résultats

        $results = $this->finder->find($query);  // Retourne des entités Prescription hydratées

        return $this->render('gestapp/prescription/searchdashboard.html.twig', [
            'prescriptions' => $results,
            'form' => $form->createView(),
        ]);
    }
}
```

### 4.3 Synchronisation SQL ↔ Elasticsearch — EventListener

Quand une prescription est supprimée, le bénéficiaire associé reste dans Elasticsearch mais son lien avec la prescription doit être mis à jour. Un EventListener Doctrine gère cette synchronisation :

```php
<?php
// src/EventListener/PrescriptionElasticaListener.php

namespace App\EventListener;

use App\Entity\Gestapp\Prescription;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

class PrescriptionElasticaListener
{
    public function __construct(
        private ObjectPersisterInterface $beneficiaryPersister
    ) {}

    // Déclenché par Doctrine après chaque suppression d'entité
    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        // Guard : ne traite que les suppressions de Prescription
        if (!$entity instanceof Prescription) {
            return;
        }

        // Mise à jour de l'index Elasticsearch pour le bénéficiaire associé
        $beneficiary = $entity->getBeneficiaire();
        if ($beneficiary) {
            // replaceOne() : réindexe entièrement le document du bénéficiaire
            $this->beneficiaryPersister->replaceOne($beneficiary);
        }
    }
}
```

Déclaration du listener dans la configuration Symfony :
```yaml
# config/services.yaml
services:
    App\EventListener\PrescriptionElasticaListener:
        tags:
            - { name: doctrine.event_listener, event: postRemove }
```

---

## 5. Comparaison SQL vs NoSQL dans ce projet

| Critère | SQL (MariaDB + Doctrine) | NoSQL (Elasticsearch) |
|---------|-------------------------|----------------------|
| **Utilisation** | CRUD, persistance, relations | Recherche, filtres dynamiques |
| **Consistance** | Source de vérité | Index secondaire (peut être re-peuplé) |
| **Requête typique** | `findBy(['step' => 'Signed'])` | `BoolQuery + Term + MatchQuery` |
| **Jointures** | OUI (QueryBuilder avec `join()`) | NON (documents dénormalisés) |
| **Temps réel** | OUI | OUI (listener defer: false) |
| **Re-population** | N/A | `fos:elastica:populate` |

---

## 6. Commandes de gestion des données

```bash
# Peupler entièrement l'index Elasticsearch depuis la BDD SQL
php bin/console fos:elastica:populate

# Peupler un index spécifique
php bin/console fos:elastica:populate --index=prescription

# Vérifier l'état des index
php bin/console fos:elastica:index

# Migrations SQL
php bin/console doctrine:migrations:migrate
php bin/console doctrine:migrations:status
```

---

## 7. Critères de performance REAC atteints

| Critère | Réalisation dans le projet |
|---------|---------------------------|
| Les composants d'accès aux données SQL sont développés | 5 repositories Doctrine avec QueryBuilder (filtres, jointures, IN/NOT IN) |
| Les requêtes sont sécurisées contre les injections SQL | Paramètres nommés Doctrine (`setParameter`), jamais de concaténation |
| Un composant d'accès NoSQL est développé | Intégration Elasticsearch via FOSElasticaBundle avec BoolQuery/Term/Match |
| La synchronisation SQL/NoSQL est assurée | PrescriptionElasticaListener (postRemove), listeners automatiques FOSElastica |
| Les requêtes sont adaptées au rôle de l'utilisateur | Filtres dynamiques selon ROLE_PRESCRIPTEUR / ROLE_MEDIATEUR / ROLE_SUPER_ADMIN |
| La performance est prise en compte | Elasticsearch pour la recherche, SQL pour la persistance fiable |

---

*Document rédigé dans le cadre de la certification Titre Professionnel DWWM*  
*Juin 2026*