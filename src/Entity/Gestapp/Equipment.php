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
    private ?string $brandEquipment = null;

    #[ORM\Column(length: 100)]
    private ?string $matriculEquipment = null;

    #[ORM\Column(length: 20)]
    private ?string $osInstalled = null;

    #[ORM\Column(length: 20)]
    private ?string $statusEquipment = null;

    #[ORM\Column]
    private ?bool $isDispo = null;

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

    public function getBrandEquipment(): ?string
    {
        return $this->brandEquipment;
    }

    public function setBrandEquipment(string $brandEquipment): static
    {
        $this->brandEquipment = $brandEquipment;

        return $this;
    }

    public function getMatriculEquipment(): ?string
    {
        return $this->matriculEquipment;
    }

    public function setMatriculEquipment(string $matriculEquipment): static
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
}
