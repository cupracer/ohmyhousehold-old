<?php

namespace App\Entity;

use App\Repository\PeriodicBookingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PeriodicBookingRepository::class)
 */
class PeriodicBooking extends BaseBooking
{
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
    private $interval;

    /**
     * @ORM\Column(type="integer")
     */
    private $bookingDayOfMonth;

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getInterval(): ?int
    {
        return $this->interval;
    }

    public function setInterval(int $interval): self
    {
        $this->interval = $interval;

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
}
