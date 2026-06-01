# Fix — Bénéficiaires absents de la recherche après suppression de prescription

## Problème

Après la suppression d'une `Prescription` liée à un `Beneficiary`, le bénéficiaire disparaissait de la vue de recherche (`/gestapp/beneficiary/`).

Les bénéficiaires existaient toujours en base de données — le problème était dans l'index Elasticsearch.

## Cause

La relation `Prescription ↔ Beneficiary` est un `OneToOne` :

- **Côté propriétaire (owning)** : `Prescription.beneficiaire` → c'est ici que la FK est stockée en base
- **Côté inverse (mappedBy)** : `Beneficiary.prescription`

FOSElasticaBundle écoute les événements Doctrine uniquement sur l'entité directement modifiée. Quand une `Prescription` est supprimée :

- ✅ Le document `prescription` est retiré de l'index Elasticsearch
- ❌ Le document `beneficiary` associé n'est **pas** mis à jour dans l'index

Résultat : le document Elasticsearch du bénéficiaire devient incohérent/orphelin et n'apparaît plus dans les résultats de recherche.

## Correction

### 1. Resynchronisation immédiate de l'index

```bash
php bin/console fos:elastica:populate --index=beneficiary
```

### 2. Correctif permanent dans `PrescriptionController`

Dans les deux actions de suppression (`delete` et `del`), le bénéficiaire associé est récupéré **avant** le flush, puis son document Elasticsearch est mis à jour **après** la suppression via `ObjectPersisterInterface::replaceOne()`.

**Fichier modifié** : `src/Controller/Gestapp/PrescriptionController.php`

```php
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// Dans delete() et del() :
$beneficiary = $prescription->getBeneficiaire();
$entityManager->remove($prescription);
$entityManager->flush();
if ($beneficiary) {
    $beneficiaryPersister->replaceOne($beneficiary);
}
```

Le persister est injecté via `#[Autowire(service: 'fos_elastica.object_persister.beneficiary')]`.
