<?php

namespace App\Entity\Gestapp;

use App\Entity\Admin\Member;
use App\Repository\Gestapp\prescriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: prescriptionRepository::class)]
class prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'prescriptions')]
    private ?Member $idMember = null;

    #[ORM\Column(length: 50)]
    private ?string $ref = null;

    #[ORM\OneToOne(mappedBy: 'prescription', cascade: ['persist', 'remove'])]
    private ?beneficiary $beneficiary = null;

    #[ORM\OneToOne(inversedBy: 'beneficiary', cascade: ['persist', 'remove'])]
    private ?beneficiary $idBenefiaciary = null;

    #[ORM\OneToOne(mappedBy: 'prescription', cascade: ['persist', 'remove'])]
    private ?equipment $equipment = null;

    #[ORM\OneToOne(inversedBy: 'prescriptions', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?equipment $idEquipment = null;

    public function __construct()
    {
        $this->idMember = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdMember(): ?Member
    {
        return $this->idMember;
    }

    public function setIdMember(?Member $idMember): static
    {
        $this->idMember = $idMember;

        return $this;
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

    public function getBeneficiary(): ?beneficiary
    {
        return $this->beneficiary;
    }

    public function setBeneficiary(beneficiary $beneficiary): static
    {
        // set the owning side of the relation if necessary
        if ($beneficiary->getPrescription() !== $this) {
            $beneficiary->setPrescription($this);
        }

        $this->beneficiary = $beneficiary;

        return $this;
    }

    public function getIdBenefiaciary(): ?beneficiary
    {
        return $this->idBenefiaciary;
    }

    public function setIdBenefiaciary(?beneficiary $idBenefiaciary): static
    {
        $this->idBenefiaciary = $idBenefiaciary;

        return $this;
    }

    public function getEquipment(): ?equipment
    {
        return $this->equipment;
    }

    public function setEquipment(equipment $equipment): static
    {
        // set the owning side of the relation if necessary
        if ($equipment->getPrescription() !== $this) {
            $equipment->setPrescription($this);
        }

        $this->equipment = $equipment;

        return $this;
    }

    public function getIdEquipment(): ?equipment
    {
        return $this->idEquipment;
    }

    public function setIdEquipment(equipment $idEquipment): static
    {
        $this->idEquipment = $idEquipment;

        return $this;
    }
}
