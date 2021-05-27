<?php

namespace App\Entity;

use App\Repository\AccountHolderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=AccountHolderRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"name", "household"},
 *     errorPath="name",
 *     message="This name is already in use in this household."
 *     )
 */
class AccountHolder implements \JsonSerializable
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
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="accountHolders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\OneToMany(targetEntity=DynamicBooking::class, mappedBy="accountHolder", orphanRemoval=true)
     */
    private $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function jsonSerialize()
    {
        return [
            "name" => $this->name,
            "createdAt" => $this->createdAt,
        ];
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
     * @return Collection|DynamicBooking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(DynamicBooking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAccountHolder($this);
        }

        return $this;
    }

    public function removeBooking(DynamicBooking $booking): self
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getAccountHolder() === $this) {
                $booking->setAccountHolder(null);
            }
        }

        return $this;
    }
}
