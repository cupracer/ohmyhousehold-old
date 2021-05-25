<?php

namespace App\Entity\DTO;

use App\Entity\AccountHolder;
use App\Entity\BookingCategory;
use Symfony\Component\Validator\Constraints as Assert;

class PeriodicBookingDTO
{

    /**
     * @var \DateTimeInterface
     */
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private $startDate;

    /**
     * @var \DateTimeInterface
     */
    #[Assert\Type("\DateTimeInterface")]
    private $endDate;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private $bookingDayOfMonth;

    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private $interval;

    /**
     * @var BookingCategory
     */
    #[Assert\NotBlank]
    private $bookingCategory;

    /**
     * @var AccountHolder
     */
    #[Assert\NotBlank]
    private $accountHolder;

    #[Assert\NotBlank]
    private $amount;

    private $description;

    private $private;

    /**
     * @return \DateTimeInterface
     */
    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param \DateTimeInterface $startDate
     * @return PeriodicBookingDTO
     */
    public function setStartDate(\DateTimeInterface $startDate): PeriodicBookingDTO
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param \DateTimeInterface $endDate
     * @return PeriodicBookingDTO
     */
    public function setEndDate(?\DateTimeInterface $endDate): PeriodicBookingDTO
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBookingDayOfMonth()
    {
        return $this->bookingDayOfMonth;
    }

    /**
     * @param mixed $bookingDayOfMonth
     * @return PeriodicBookingDTO
     */
    public function setBookingDayOfMonth($bookingDayOfMonth)
    {
        $this->bookingDayOfMonth = $bookingDayOfMonth;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param mixed $interval
     * @return PeriodicBookingDTO
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return BookingCategory
     */
    public function getBookingCategory(): ?BookingCategory
    {
        return $this->bookingCategory;
    }

    /**
     * @param BookingCategory $bookingCategory
     * @return PeriodicBookingDTO
     */
    public function setBookingCategory(BookingCategory $bookingCategory): PeriodicBookingDTO
    {
        $this->bookingCategory = $bookingCategory;
        return $this;
    }

    /**
     * @return AccountHolder
     */
    public function getAccountHolder(): ?AccountHolder
    {
        return $this->accountHolder;
    }

    /**
     * @param AccountHolder $accountHolder
     * @return PeriodicBookingDTO
     */
    public function setAccountHolder(AccountHolder $accountHolder): PeriodicBookingDTO
    {
        $this->accountHolder = $accountHolder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     * @return PeriodicBookingDTO
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return PeriodicBookingDTO
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @param mixed $private
     * @return PeriodicBookingDTO
     */
    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }
}
