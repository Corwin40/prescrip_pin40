<?php

namespace App\Entity\Gestapp;

use App\Entity\Admin\Member;
use App\Repository\Gestapp\PrescriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrescriptionRepository::class)]
class Prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $ref = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'prescriptions')]
    private ?Member $membre = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Beneficiary $beneficiaire = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Equipment $equipement = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(length: 100)]
    private ?string $baseCompetence = null;

    #[ORM\Column(length: 100)]
    private ?string $lieuMediation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): static
    {
        $this->ref = $ref;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getMembre(): ?Member
    {
        return $this->membre;
    }

    public function setMembre(?Member $membre): static
    {
        $this->membre = $membre;

        return $this;
    }

    public function getBeneficiaire(): ?Beneficiary
    {
        return $this->beneficiaire;
    }

    public function setBeneficiaire(?Beneficiary $beneficiaire): static
    {
        $this->beneficiaire = $beneficiaire;

        return $this;
    }

    public function getEquipement(): ?Equipment
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipment $equipement): static
    {
        $this->equipement = $equipement;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getBaseCompetence(): ?string
    {
        return $this->baseCompetence;
    }

    public function setBaseCompetence(string $baseCompetence): static
    {
        $this->baseCompetence = $baseCompetence;

        return $this;
    }

    public function getLieuMediation(): ?string
    {
        return $this->lieuMediation;
    }

    public function setLieuMediation(string $lieuMediation): static
    {
        $this->lieuMediation = $lieuMediation;

        return $this;
    }
}
