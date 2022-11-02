<?php

namespace App\Entity\Supplies;

use App\Entity\Household;
use App\Repository\Supplies\StorageLocationRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=StorageLocationRepository::class)
 * @ORM\Table(name="supplies_storage_location")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"name", "household"},
 *     errorPath="name",
 *     message="This name is already in use in this household."
 *     )
 */
class StorageLocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="supplyStorageLocations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\OneToMany(targetEntity=Item::class, mappedBy="storageLocation")
     */
    private $supplyItems;

    public function __construct()
    {
        $this->supplyItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getHousehold(): ?Household
    {
        return $this->household;
    }

    public function setHousehold(?Household $household): self
    {
        $this->household = $household;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplyItems(): Collection
    {
        return $this->supplyItems;
    }

    public function addSupplyItem(Item $supplyItem): self
    {
        if (!$this->supplyItems->contains($supplyItem)) {
            $this->supplyItems[] = $supplyItem;
            $supplyItem->setStorageLocation($this);
        }

        return $this;
    }

    public function removeSupplyItem(Item $supplyItem): self
    {
        if ($this->supplyItems->removeElement($supplyItem)) {
            // set the owning side to null (unless already changed)
            if ($supplyItem->getStorageLocation() === $this) {
                $supplyItem->setStorageLocation(null);
            }
        }

        return $this;
    }
}
