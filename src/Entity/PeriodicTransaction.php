<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 */
abstract class PeriodicTransaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $household;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $bookingInterval;

    /**
     * @ORM\Column(type="integer")
     */
    private $bookingDayOfMonth;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    protected $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $private;

    /**
     * @ORM\Column(type="integer")
     */
    private $bookingPeriodOffset;

    public function jsonSerialize()
    {
        return [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new DateTime();
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getBookingInterval(): ?int
    {
        return $this->bookingInterval;
    }

    public function setBookingInterval(int $bookingInterval): self
    {
        $this->bookingInterval = $bookingInterval;

        return $this;
    }

    public function getBookingDayOfMonth(): ?int
    {
        return $this->bookingDayOfMonth;
    }

    public function setBookingDayOfMonth(int $bookingDayOfMonth): self
    {
        $this->bookingDayOfMonth = $bookingDayOfMonth;

        return $this;
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

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getBookingPeriodOffset(): ?int
    {
        return $this->bookingPeriodOffset;
    }

    public function setBookingPeriodOffset(int $bookingPeriodOffset): self
    {
        $this->bookingPeriodOffset = $bookingPeriodOffset;

        return $this;
    }
}
