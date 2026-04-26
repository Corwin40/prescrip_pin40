<?php

namespace App\Entity\Serv;

use App\Entity\Gestapp\Prescription;
use App\Repository\Serv\DocusealRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocusealRepository::class)]
class Docuseal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idSeal = null;

    #[ORM\Column(length: 20)]
    private ?string $slugSeal = null;

    #[ORM\Column(length: 40)]
    private ?string $uuidSeal = null;

    #[ORM\Column(length: 80)]
    private ?string $nameSubmissionSeal = null;

    #[ORM\Column(length: 100)]
    private ?string $emailSubmissionSeal = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $phoneSeal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $completedAtSeal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $declinedAtSeal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $sentAtSeal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $createdAtSeal = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $statusSeal = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $valuesSeal = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $embed_SrcSeal = null;

    #[ORM\OneToOne(inversedBy: 'docuseal', cascade: ['persist', 'remove'])]
    private ?Prescription $prescription = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $updatedAtSeal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $openedAtSeal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdSeal(): ?int
    {
        return $this->idSeal;
    }

    public function setIdSeal(int $idSeal): static
    {
        $this->idSeal = $idSeal;

        return $this;
    }

    public function getSlugSeal(): ?string
    {
        return $this->slugSeal;
    }

    public function setSlugSeal(string $slugSeal): static
    {
        $this->slugSeal = $slugSeal;

        return $this;
    }

    public function getUuidSeal(): ?string
    {
        return $this->uuidSeal;
    }

    public function setUuidSeal(string $uuidSeal): static
    {
        $this->uuidSeal = $uuidSeal;

        return $this;
    }

    public function getNameSubmissionSeal(): ?string
    {
        return $this->nameSubmissionSeal;
    }

    public function setNameSubmissionSeal(string $nameSubmissionSeal): static
    {
        $this->nameSubmissionSeal = $nameSubmissionSeal;

        return $this;
    }

    public function getEmailSubmissionSeal(): ?string
    {
        return $this->emailSubmissionSeal;
    }

    public function setEmailSubmissionSeal(string $emailSubmissionSeal): static
    {
        $this->emailSubmissionSeal = $emailSubmissionSeal;

        return $this;
    }

    public function getPhoneSeal(): ?string
    {
        return $this->phoneSeal;
    }

    public function setPhoneSeal(?string $phoneSeal): static
    {
        $this->phoneSeal = $phoneSeal;

        return $this;
    }

    public function getCompletedAtSeal(): ?\DateTime
    {
        return $this->completedAtSeal;
    }

    public function setCompletedAtSeal(?\DateTime $completedAtSeal): static
    {
        $this->completedAtSeal = $completedAtSeal;

        return $this;
    }

    public function getDeclinedAtSeal(): ?\DateTime
    {
        return $this->declinedAtSeal;
    }

    public function setDeclinedAtSeal(?\DateTime $declinedAtSeal): static
    {
        $this->declinedAtSeal = $declinedAtSeal;

        return $this;
    }

    public function getSentAtSeal(): ?\DateTime
    {
        return $this->sentAtSeal;
    }

    public function setSentAtSeal(?\DateTime $sentAtSeal): static
    {
        $this->sentAtSeal = $sentAtSeal;

        return $this;
    }

    public function getCreatedAtSeal(): ?\DateTime
    {
        return $this->createdAtSeal;
    }

    public function setCreatedAtSeal(?\DateTime $createdAtSeal): static
    {
        $this->createdAtSeal = $createdAtSeal;

        return $this;
    }

    public function getStatusSeal(): ?string
    {
        return $this->statusSeal;
    }

    public function setStatusSeal(?string $statusSeal): static
    {
        $this->statusSeal = $statusSeal;

        return $this;
    }

    public function getValuesSeal(): ?array
    {
        return $this->valuesSeal;
    }

    public function setValuesSeal(?array $valuesSeal): static
    {
        $this->valuesSeal = $valuesSeal;

        return $this;
    }

    public function getEmbedSrcSeal(): ?string
    {
        return $this->embed_SrcSeal;
    }

    public function setEmbedSrcSeal(?string $embed_SrcSeal): static
    {
        $this->embed_SrcSeal = $embed_SrcSeal;

        return $this;
    }

    public function getPrescription(): ?Prescription
    {
        return $this->prescription;
    }

    public function setPrescription(?Prescription $prescription): static
    {
        $this->prescription = $prescription;

        return $this;
    }

    public function getUpdatedAtSeal(): ?\DateTime
    {
        return $this->updatedAtSeal;
    }

    public function setUpdatedAtSeal(?\DateTime $updatedAtSeal): static
    {
        $this->updatedAtSeal = $updatedAtSeal;

        return $this;
    }

    public function getOpenedAtSeal(): ?\DateTime
    {
        return $this->openedAtSeal;
    }

    public function setOpenedAtSeal(?\DateTime $openedAtSeal): static
    {
        $this->openedAtSeal = $openedAtSeal;

        return $this;
    }
}
