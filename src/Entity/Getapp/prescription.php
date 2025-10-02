<?php

namespace App\Entity\Getapp;

use App\Repository\Getapp\prescriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: prescriptionRepository::class)]
class prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $userPrescribFirstname = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $userPrescribLastname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserPrescribFirstname(): ?string
    {
        return $this->userPrescribFirstname;
    }

    public function setUserPrescribFirstname(?string $userPrescribFirstname): static
    {
        $this->userPrescribFirstname = $userPrescribFirstname;

        return $this;
    }

    public function getUserPrescribLastname(): ?string
    {
        return $this->userPrescribLastname;
    }

    public function setUserPrescribLastname(?string $userPrescribLastname): static
    {
        $this->userPrescribLastname = $userPrescribLastname;

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
}
