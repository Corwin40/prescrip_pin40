<?php

namespace App\Entity\Gestapp;

use App\Config\StatusPrescription;
use App\Config\StepPrescription;
use App\Entity\Admin\Member;
use App\Entity\Admin\Structure;
use App\Entity\Serv\Docuseal;
use App\Repository\Gestapp\PrescriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrescriptionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prescriptions')]
    private ?Structure $prescriptor = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Equipment $equipement = null;

    #[ORM\Column(length: 100)]
    private ?string $ref = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $objectName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(length: 100)]
    private ?string $baseCompetence = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $compteur = null;

    #[ORM\ManyToOne(inversedBy: 'lieuxmediation')]
    private ?Structure $lieuMediation = null;

    #[ORM\OneToOne(inversedBy: 'prescription', cascade: ['persist'])]
    private ?Beneficiary $beneficiaire = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Competence $competence = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $commune = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $cp = null;

    #[ORM\Column]
    private ?bool $validcase = false;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $closedAt = null;

    #[ORM\Column]
    private ?bool $isOpenByPrescriptor = false;

    #[ORM\Column(nullable: true)]
    private ?bool $isOpenByMediator = false;

    #[ORM\Column(enumType: StepPrescription::class)]
    private ?StepPrescription $step = null;

    #[ORM\Column(enumType: StatusPrescription::class)]
    private ?StatusPrescription $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'prescription')]
    private Collection $documents;

    #[ORM\OneToOne(mappedBy: 'prescription', cascade: ['persist', 'remove'])]
    private ?Docuseal $docuseal = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $createdAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

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

    public function getPrescriptor(): ?Structure
    {
        return $this->prescriptor;
    }

    public function setPrescriptor(?Structure $prescriptor): static
    {
        $this->prescriptor = $prescriptor;

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

    public function getCompteur(): ?int
    {
        return $this->compteur;
    }

    public function setCompteur(int $compteur): static
    {
        $this->compteur = $compteur;

        return $this;
    }

    public function getLieuMediation(): ?Structure
    {
        return $this->lieuMediation;
    }

    public function setLieuMediation(?Structure $lieuMediation): static
    {
        $this->lieuMediation = $lieuMediation;

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

    public function getCompetence(): ?Competence
    {
        return $this->competence;
    }

    public function setCompetence(?Competence $competence): static
    {
        $this->competence = $competence;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(string $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getCp(): ?string
    {
        return $this->cp;
    }

    public function setCp(string $cp): static
    {
        $this->cp = $cp;

        return $this;
    }

    public function isValidcase(): ?bool
    {
        return $this->validcase;
    }

    public function setValidcase(bool $validcase): static
    {
        $this->validcase = $validcase;

        return $this;
    }

    public function isOpenByPrescriptor(): ?bool
    {
        return $this->isOpenByPrescriptor;
    }

    public function setIsOpenByPrescriptor(bool $isOpenByPrescriptor): static
    {
        $this->isOpenByPrescriptor = $isOpenByPrescriptor;

        return $this;
    }

    public function isOpenByMediator(): ?bool
    {
        return $this->isOpenByMediator;
    }

    public function setIsOpenByMediator(?bool $isOpenByMediator): static
    {
        $this->isOpenByMediator = $isOpenByMediator;

        return $this;
    }

    public function getStep(): ?StepPrescription
    {
        return $this->step;
    }

    public function setStep(StepPrescription $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function getStatus(): ?StatusPrescription
    {
        return $this->status;
    }

    public function setStatus(StatusPrescription $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getObjectName(): ?string
    {
        return $this->objectName;
    }

    public function setObjectName(?string $objectName): static
    {
        $this->objectName = $objectName;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setPrescription($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getPrescription() === $this) {
                $document->setPrescription(null);
            }
        }

        return $this;
    }

    public function getDocuseal(): ?Docuseal
    {
        return $this->docuseal;
    }

    public function setDocuseal(?Docuseal $docuseal): static
    {
        // unset the owning side of the relation if necessary
        if ($docuseal === null && $this->docuseal !== null) {
            $this->docuseal->setPrescription(null);
        }

        // set the owning side of the relation if necessary
        if ($docuseal !== null && $docuseal->getPrescription() !== $this) {
            $docuseal->setPrescription($this);
        }

        $this->docuseal = $docuseal;

        return $this;
    }

    public function getClosedAt(): ?\DateTime
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTime $closedAt): static
    {
        $this->closedAt = $closedAt;

        return $this;
    }
}
