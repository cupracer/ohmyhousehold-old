<?php

namespace App\Entity;

use App\Repository\AccountHolderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
class BaseBooking
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="accountHolders")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $household;

    /**
     * @ORM\ManyToOne(targetEntity=HouseholdUser::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $householdUser;

    /**
     * @ORM\ManyToOne(targetEntity=BookingCategory::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $bookingCategory;

    /**
     * @ORM\ManyToOne(targetEntity=AccountHolder::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $accountHolder;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHouseholdUser(): ?HouseholdUser
    {
        return $this->householdUser;
    }

    public function setHouseholdUser(?HouseholdUser $householdUser): self
    {
        $this->householdUser = $householdUser;

        return $this;
    }

    public function getBookingCategory(): ?BookingCategory
    {
        return $this->bookingCategory;
    }

    public function setBookingCategory(?BookingCategory $bookingCategory): self
    {
        $this->bookingCategory = $bookingCategory;

        return $this;
    }

    public function getAccountHolder(): ?AccountHolder
    {
        return $this->accountHolder;
    }

    public function setAccountHolder(?AccountHolder $accountHolder): self
    {
        $this->accountHolder = $accountHolder;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
