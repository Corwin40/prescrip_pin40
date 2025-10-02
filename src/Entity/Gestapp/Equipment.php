<?php

namespace App\Entity\Gestapp;

use App\Repository\Gestapp\EquipmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $typeEquipment = null;

    #[ORM\Column(length: 20)]
    private ?string $computerBrand = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $matriculEquipment = null;

    #[ORM\Column(length: 20)]
    private ?string $osInstalled = null;

    #[ORM\Column(length: 20)]
    private ?string $statusEquipment = null;

    #[ORM\Column]
    private ?bool $isDispo = null;

    #[ORM\OneToOne(inversedBy: 'equipment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?prescription $prescription = null;

    #[ORM\OneToOne(mappedBy: 'idEquipment', cascade: ['persist', 'remove'])]
    private ?prescription $prescriptions = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeEquipment(): ?string
    {
        return $this->typeEquipment;
    }

    public function setTypeEquipment(string $typeEquipment): static
    {
        $this->typeEquipment = $typeEquipment;

        return $this;
    }

    public function getComputerBrand(): ?string
    {
        return $this->computerBrand;
    }

    public function setComputerBrand(string $computerBrand): static
    {
        $this->computerBrand = $computerBrand;

        return $this;
    }

    public function getMatriculEquipment(): ?string
    {
        return $this->matriculEquipment;
    }

    public function setMatriculEquipment(?string $matriculEquipment): static
    {
        $this->matriculEquipment = $matriculEquipment;

        return $this;
    }

    public function getOsInstalled(): ?string
    {
        return $this->osInstalled;
    }

    public function setOsInstalled(string $osInstalled): static
    {
        $this->osInstalled = $osInstalled;

        return $this;
    }

    public function getStatusEquipment(): ?string
    {
        return $this->statusEquipment;
    }

    public function setStatusEquipment(string $statusEquipment): static
    {
        $this->statusEquipment = $statusEquipment;

        return $this;
    }

    public function isDispo(): ?bool
    {
        return $this->isDispo;
    }

    public function setIsDispo(bool $isDispo): static
    {
        $this->isDispo = $isDispo;

        return $this;
    }

    public function getPrescription(): ?prescription
    {
        return $this->prescription;
    }

    public function setPrescription(prescription $prescription): static
    {
        $this->prescription = $prescription;

        return $this;
    }

    public function getPrescriptions(): ?prescription
    {
        return $this->prescriptions;
    }

    public function setPrescriptions(prescription $prescriptions): static
    {
        // set the owning side of the relation if necessary
        if ($prescriptions->getIdEquipment() !== $this) {
            $prescriptions->setIdEquipment($this);
        }

        $this->prescriptions = $prescriptions;

        return $this;
    }

    public function __toString(): string
    {
        return $this->matriculEquipment;
    }
}
