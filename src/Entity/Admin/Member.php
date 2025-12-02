<?php

namespace App\Entity\Admin;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Gestapp\Prescription;
use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ApiResource]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: '`member`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[ORM\HasLifecycleCallbacks]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 150)]
    private ?string $nameStructure = null;

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
    private ?string $ContactResponsableFirstname = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contactResponsableLastname = null;

    #[ORM\Column(length: 4, nullable: true)]
    private ?string $contactResponsableCivility = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Prescription>
     */
    #[ORM\OneToMany(targetEntity: Prescription::class, mappedBy: 'membre')]
    private Collection $prescriptions;

    /**
     * @var Collection<int, Prescription>
     */
    #[ORM\OneToMany(targetEntity: Prescription::class, mappedBy: 'lieuMediation')]
    private Collection $lieuxmediation;

    public function __construct()
    {
        $this->prescriptions = new ArrayCollection();
        $this->lieuxmediation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getNameStructure(): ?string
    {
        return $this->nameStructure;
    }

    public function setNameStructure(string $nameStructure): static
    {
        $this->nameStructure = $nameStructure;
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
        return $this->ContactResponsableFirstname;
    }

    public function setContactResponsableFirstname(?string $ContactResponsableFirstname): static
    {
        $this->ContactResponsableFirstname = $ContactResponsableFirstname;
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

    public function getContactResponsableCivility(): ?string
    {
        return $this->contactResponsableCivility;
    }

    public function setContactResponsableCivility(?string $contactResponsableCivility): static
    {
        $this->contactResponsableCivility = $contactResponsableCivility;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
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
            $prescription->setMembre($this);
        }

        return $this;
    }

    public function removePrescription(Prescription $prescription): static
    {
        if ($this->prescriptions->removeElement($prescription)) {
            if ($prescription->getMembre() === $this) {
                $prescription->setMembre(null);
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

    public function __toString(){
        return $this->nameStructure;
    }
}
