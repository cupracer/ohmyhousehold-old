<?php

namespace App\Entity;

use App\Repository\HouseholdRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HouseholdRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Household
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=HouseholdUser::class, mappedBy="household", orphanRemoval=true)
     */
    private $householdUsers;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=AccountHolder::class, mappedBy="household", orphanRemoval=true)
     */
    private $accountHolders;

    /**
     * @ORM\OneToMany(targetEntity=BookingCategory::class, mappedBy="household", orphanRemoval=true)
     */
    private $bookingCategories;

    public function __construct()
    {
        $this->householdUsers = new ArrayCollection();
        $this->accountHolders = new ArrayCollection();
        $this->bookingCategories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|HouseholdUser[]
     */
    public function getHouseholdUsers(): Collection
    {
        return $this->householdUsers;
    }

    public function addHouseholdUser(HouseholdUser $householdUser): self
    {
        if (!$this->householdUsers->contains($householdUser)) {
            $this->householdUsers[] = $householdUser;
            $householdUser->setHousehold($this);
        }

        return $this;
    }

    public function removeHouseholdUser(HouseholdUser $householdUser): self
    {
        if ($this->householdUsers->removeElement($householdUser)) {
            // set the owning side to null (unless already changed)
            if ($householdUser->getHousehold() === $this) {
                $householdUser->setHousehold(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return Collection|AccountHolder[]
     */
    public function getAccountHolders(): Collection
    {
        return $this->accountHolders;
    }

    public function addAccountHolder(AccountHolder $accountHolder): self
    {
        if (!$this->accountHolders->contains($accountHolder)) {
            $this->accountHolders[] = $accountHolder;
            $accountHolder->setHousehold($this);
        }

        return $this;
    }

    public function removeAccountHolder(AccountHolder $accountHolder): self
    {
        if ($this->accountHolders->removeElement($accountHolder)) {
            // set the owning side to null (unless already changed)
            if ($accountHolder->getHousehold() === $this) {
                $accountHolder->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|BookingCategory[]
     */
    public function getBookingCategories(): Collection
    {
        return $this->bookingCategories;
    }

    public function addBookingCategory(BookingCategory $bookingCategory): self
    {
        if (!$this->bookingCategories->contains($bookingCategory)) {
            $this->bookingCategories[] = $bookingCategory;
            $bookingCategory->setHousehold($this);
        }

        return $this;
    }

    public function removeBookingCategory(BookingCategory $bookingCategory): self
    {
        if ($this->bookingCategories->removeElement($bookingCategory)) {
            // set the owning side to null (unless already changed)
            if ($bookingCategory->getHousehold() === $this) {
                $bookingCategory->setHousehold(null);
            }
        }

        return $this;
    }
}
