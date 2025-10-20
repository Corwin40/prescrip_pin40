<?php

namespace App\Entity\Gestapp;

use App\Repository\Gestapp\CompetenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetenceRepository::class)]
class Competence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $compBase = null;

    #[ORM\Column(length: 100)]
    private ?string $compDesk = null;

    #[ORM\Column(length: 100)]
    private ?string $compInternet = null;

    #[ORM\Column(length: 100)]
    private ?string $compEmail = null;

    #[ORM\Column]
    private ?bool $isAutoEva = null;

    #[ORM\Column]
    private ?bool $isDigComp0 = null;

    #[ORM\Column]
    private ?bool $isDigComp1 = null;

    #[ORM\Column]
    private ?bool $isDigComp2 = null;

    #[ORM\Column]
    private ?bool $isDigComp3 = null;

    #[ORM\Column]
    private ?bool $isDigComp4 = null;

    #[ORM\Column]
    private ?bool $isDigComp5 = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $detailParcour = null;

    #[ORM\Column]
    private ?bool $isAutoEvalEnd = null;

    #[ORM\OneToOne(mappedBy: 'beneficiaryCompetences', cascade: ['persist', 'remove'])]
    private ?Beneficiary $beneficiary = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompBase(): ?string
    {
        return $this->compBase;
    }

    public function setCompBase(string $compBase): static
    {
        $this->compBase = $compBase;

        return $this;
    }

    public function getCompDesk(): ?string
    {
        return $this->compDesk;
    }

    public function setCompDesk(string $compDesk): static
    {
        $this->compDesk = $compDesk;

        return $this;
    }

    public function getCompInternet(): ?string
    {
        return $this->compInternet;
    }

    public function setCompInternet(string $compInternet): static
    {
        $this->compInternet = $compInternet;

        return $this;
    }

    public function getCompEmail(): ?string
    {
        return $this->compEmail;
    }

    public function setCompEmail(string $compEmail): static
    {
        $this->compEmail = $compEmail;

        return $this;
    }

    public function isAutoEva(): ?bool
    {
        return $this->isAutoEva;
    }

    public function setIsAutoEva(bool $isAutoEva): static
    {
        $this->isAutoEva = $isAutoEva;

        return $this;
    }

    public function isDigComp0(): ?bool
    {
        return $this->isDigComp0;
    }

    public function setIsDigComp0(bool $isDigComp0): static
    {
        $this->isDigComp0 = $isDigComp0;

        return $this;
    }

    public function isDigComp1(): ?bool
    {
        return $this->isDigComp1;
    }

    public function setIsDigComp1(bool $isDigComp1): static
    {
        $this->isDigComp1 = $isDigComp1;

        return $this;
    }

    public function isDigComp2(): ?bool
    {
        return $this->isDigComp2;
    }

    public function setIsDigComp2(bool $isDigComp2): static
    {
        $this->isDigComp2 = $isDigComp2;

        return $this;
    }

    public function isDigComp3(): ?bool
    {
        return $this->isDigComp3;
    }

    public function setIsDigComp3(bool $isDigComp3): static
    {
        $this->isDigComp3 = $isDigComp3;

        return $this;
    }

    public function isDigComp4(): ?bool
    {
        return $this->isDigComp4;
    }

    public function setIsDigComp4(bool $isDigComp4): static
    {
        $this->isDigComp4 = $isDigComp4;

        return $this;
    }

    public function isDigComp5(): ?bool
    {
        return $this->isDigComp5;
    }

    public function setIsDigComp5(bool $isDigComp5): static
    {
        $this->isDigComp5 = $isDigComp5;

        return $this;
    }

    public function getDetailParcour(): ?string
    {
        return $this->detailParcour;
    }

    public function setDetailParcour(string $detailParcour): static
    {
        $this->detailParcour = $detailParcour;

        return $this;
    }

    public function isAutoEvalEnd(): ?bool
    {
        return $this->isAutoEvalEnd;
    }

    public function setIsAutoEvalEnd(bool $isAutoEvalEnd): static
    {
        $this->isAutoEvalEnd = $isAutoEvalEnd;

        return $this;
    }

    public function getBeneficiary(): ?Beneficiary
    {
        return $this->beneficiary;
    }

    public function setBeneficiary(?Beneficiary $beneficiary): static
    {
        // unset the owning side of the relation if necessary
        if ($beneficiary === null && $this->beneficiary !== null) {
            $this->beneficiary->setBeneficiaryCompetences(null);
        }

        // set the owning side of the relation if necessary
        if ($beneficiary !== null && $beneficiary->getBeneficiaryCompetences() !== $this) {
            $beneficiary->setBeneficiaryCompetences($this);
        }

        $this->beneficiary = $beneficiary;

        return $this;
    }
}
