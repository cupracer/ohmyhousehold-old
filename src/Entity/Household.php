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

    public function __construct()
    {
        $this->householdUsers = new ArrayCollection();
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
}
