<?php

namespace App\Entity\Gestapp;

use App\Repository\Gestapp\EquipmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[ORM\HasLifecycleCallbacks]
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime('now');
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setUpdatedAt(): self
    {
        $this->updatedAt = new \DateTime('now');
        return $this;
    }
}
