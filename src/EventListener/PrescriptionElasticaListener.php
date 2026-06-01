<?php

namespace App\EventListener;

use App\Entity\Gestapp\Prescription;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;

class PrescriptionElasticaListener
{
    public function __construct(private ObjectPersisterInterface $beneficiaryPersister)
    {
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Prescription) {
            return;
        }

        $beneficiary = $entity->getBeneficiaire();
        if ($beneficiary) {
            $this->beneficiaryPersister->replaceOne($beneficiary);
        }
    }
}
