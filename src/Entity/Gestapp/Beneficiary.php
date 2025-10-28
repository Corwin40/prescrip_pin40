<?php

namespace App\Entity\Gestapp;

use App\Repository\Gestapp\BeneficiaryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BeneficiaryRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Beneficiary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $firstname = null;

    #[ORM\Column(length: 100)]
    private ?string $lastname = null;

    #[ORM\Column(length: 6)]
    private ?string $civility = null;

    #[ORM\Column(length: 10)]
    private ?string $gender = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $ageGroup = null;

    #[ORM\Column(length: 40)]
    private ?string $professionnalStatus = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToOne(inversedBy: 'beneficiary', cascade: ['persist', 'remove'])]
    private ?Competence $beneficiaryCompetences = null;

    #[ORM\OneToOne(mappedBy: 'beneficiaire', cascade: ['persist', 'remove'])]
    private ?Prescription $prescription = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(string $civility): static
    {
        $this->civility = $civility;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getAgeGroup(): ?string
    {
        return $this->ageGroup;
    }

    public function setAgeGroup(?string $ageGroup): static
    {
        $this->ageGroup = $ageGroup;
        return $this;
    }

    public function getProfessionnalStatus(): ?string
    {
        return $this->professionnalStatus;
    }

    public function setProfessionnalStatus(string $professionnalStatus): static
    {
        $this->professionnalStatus = $professionnalStatus;
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

    public function getBeneficiaryCompetences(): ?Competence
    {
        return $this->beneficiaryCompetences;
    }

    public function setBeneficiaryCompetences(?Competence $beneficiaryCompetences): static
    {
        $this->beneficiaryCompetences = $beneficiaryCompetences;
        return $this;
    }

    public function getPrescription(): ?Prescription
    {
        return $this->prescription;
    }

    public function setPrescription(?Prescription $prescription): static
    {
        // unset the owning side of the relation if necessary
        if ($prescription === null && $this->prescription !== null) {
            $this->prescription->setBeneficiaire(null);
        }

        // set the owning side of the relation if necessary
        if ($prescription !== null && $prescription->getBeneficiaire() !== $this) {
            $prescription->setBeneficiaire($this);
        }

        $this->prescription = $prescription;

        return $this;
    }
}
