<?php

namespace App\Entity\Admin;

use App\Config\Civility;
use App\Entity\Gestapp\Beneficiary;
use App\Entity\Gestapp\Prescription;
use App\Repository\Admin\StructureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StructureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Structure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contactResponsableFirstname = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contactResponsableLastname = null;

    #[ORM\Column(enumType: Civility::class)]
    private ?Civility $contactResponsableCivility = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Member>
     */
    #[ORM\OneToMany(targetEntity: Member::class, mappedBy: 'structure')]
    private Collection $members;

    /**
     * @var Collection<int, Beneficiary>
     */
    #[ORM\OneToMany(targetEntity: Beneficiary::class, mappedBy: 'structure', cascade: ['persist'])]
    private Collection $beneficiaries;

    /**
     * @var Collection<int, Prescription>
     */
    #[ORM\OneToMany(targetEntity: Prescription::class, mappedBy: 'lieuMediation')]
    private Collection $lieuxmediation;

    /**
     * @var Collection<int, Prescription>
     */
    #[ORM\OneToMany(targetEntity: Prescription::class, mappedBy: 'prescriptor')]
    private Collection $prescriptions;

    public function __construct()
    {
        $this->prescriptions = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->beneficiaries = new ArrayCollection();
        $this->lieuxmediation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): static
    {
        $this->zipcode = $zipcode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    public function getContactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function setContactPhone(?string $contactPhone): static
    {
        $this->contactPhone = $contactPhone;
        return $this;
    }

    public function getContactResponsableFirstname(): ?string
    {
        return $this->contactResponsableFirstname;
    }

    public function setContactResponsableFirstname(?string $contactResponsableFirstname): static
    {
        $this->contactResponsableFirstname = $contactResponsableFirstname;
        return $this;
    }

    public function getContactResponsableLastname(): ?string
    {
        return $this->contactResponsableLastname;
    }

    public function setContactResponsableLastname(?string $contactResponsableLastname): static
    {
        $this->contactResponsableLastname = $contactResponsableLastname;
        return $this;
    }

    public function getContactResponsableCivility(): ?Civility
    {
        return $this->contactResponsableCivility;
    }

    public function setContactResponsableCivility(?Civility $contactResponsableCivility): static
    {
        $this->contactResponsableCivility = $contactResponsableCivility;
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

    public function __toString(){
        return $this->name;
    }

    /**
     * @return Collection<int, Member>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setStructure($this);
        }

        return $this;
    }

    public function removeMember(Member $member): static
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getStructure() === $this) {
                $member->setStructure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Beneficiary>
     */
    public function getBeneficiaries(): Collection
    {
        return $this->beneficiaries;
    }

    public function addBeneficiary(Beneficiary $beneficiary): static
    {
        if (!$this->beneficiaries->contains($beneficiary)) {
            $this->beneficiaries->add($beneficiary);
            $beneficiary->setStructure($this);
        }

        return $this;
    }

    public function removeBeneficiary(Beneficiary $beneficiary): static
    {
        if ($this->beneficiaries->removeElement($beneficiary)) {
            // set the owning side to null (unless already changed)
            if ($beneficiary->getStructure() === $this) {
                $beneficiary->setStructure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Prescription>
     */
    public function getLieuxmediation(): Collection
    {
        return $this->lieuxmediation;
    }

    public function addLieuxmediation(Prescription $lieuxmediation): static
    {
        if (!$this->lieuxmediation->contains($lieuxmediation)) {
            $this->lieuxmediation->add($lieuxmediation);
            $lieuxmediation->setLieuMediation($this);
        }

        return $this;
    }

    public function removeLieuxmediation(Prescription $lieuxmediation): static
    {
        if ($this->lieuxmediation->removeElement($lieuxmediation)) {
            // set the owning side to null (unless already changed)
            if ($lieuxmediation->getLieuMediation() === $this) {
                $lieuxmediation->setLieuMediation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Prescription>
     */
    public function getPrescriptions(): Collection
    {
        return $this->prescriptions;
    }

    public function addPrescription(Prescription $prescription): static
    {
        if (!$this->prescriptions->contains($prescription)) {
            $this->prescriptions->add($prescription);
            $prescription->setPrescriptor($this);
        }

        return $this;
    }

    public function removePrescription(Prescription $prescription): static
    {
        if ($this->prescriptions->removeElement($prescription)) {
            if ($prescription->getPrescriptor() === $this) {
                $prescription->setPrescriptor(null);
            }
        }

        return $this;
    }
}
