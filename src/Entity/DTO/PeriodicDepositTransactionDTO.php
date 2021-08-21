<?php

namespace App\Entity\DTO;

use App\Entity\AccountHolder;
use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use Symfony\Component\Validator\Constraints as Assert;

class PeriodicDepositTransactionDTO
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

    /**
     * @var BookingCategory
     */
    #[Assert\NotBlank]
    private $bookingCategory;

    /**
     * @var AccountHolder
     */
    #[Assert\NotBlank]
    private $source;

    /**
     * @var AssetAccount
     */
    #[Assert\NotBlank]
    private $destination;

    #[Assert\NotBlank]
    private $amount;

    private $description;

    private $private;

    private int $bookingPeriodOffset;

    /**
     * @var int
     */
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private $bookingInterval;

    /**
     * @var int
     */
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    private $bookingDayOfMonth;

    public function __construct()
    {
        $this->bookingInterval = 1;
        $this->bookingDayOfMonth = 1;
        $this->bookingPeriodOffset = 0;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param \DateTimeInterface $startDate
     * @return PeriodicDepositTransactionDTO
     */
    public function setStartDate(\DateTimeInterface $startDate): PeriodicDepositTransactionDTO
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param \DateTimeInterface|null $endDate
     * @return PeriodicDepositTransactionDTO
     */
    public function setEndDate(?\DateTimeInterface $endDate): PeriodicDepositTransactionDTO
    {
        $this->endDate = $endDate;
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
     * @return PeriodicDepositTransactionDTO
     */
    public function setBookingCategory(BookingCategory $bookingCategory): PeriodicDepositTransactionDTO
    {
        $this->bookingCategory = $bookingCategory;
        return $this;
    }

    /**
     * @return AccountHolder|null
     */
    public function getSource(): ?AccountHolder
    {
        return $this->source;
    }

    /**
     * @param AccountHolder $source
     * @return PeriodicDepositTransactionDTO
     */
    public function setSource(AccountHolder $source): PeriodicDepositTransactionDTO
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return AssetAccount|null
     */
    public function getDestination(): ?AssetAccount
    {
        return $this->destination;
    }

    /**
     * @param AssetAccount $destination
     * @return PeriodicDepositTransactionDTO
     */
    public function setDestination(AssetAccount $destination): PeriodicDepositTransactionDTO
    {
        $this->destination = $destination;
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
     * @return PeriodicDepositTransactionDTO
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
     * @return PeriodicDepositTransactionDTO
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
     * @return PeriodicDepositTransactionDTO
     */
    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }

    /**
     * @return int
     */
    public function getBookingInterval(): int
    {
        return $this->bookingInterval;
    }

    /**
     * @param int $bookingInterval
     * @return PeriodicDepositTransactionDTO
     */
    public function setBookingInterval(int $bookingInterval): PeriodicDepositTransactionDTO
    {
        $this->bookingInterval = $bookingInterval;
        return $this;
    }

    /**
     * @return int
     */
    public function getBookingDayOfMonth(): int
    {
        return $this->bookingDayOfMonth;
    }

    /**
     * @param int $bookingDayOfMonth
     * @return PeriodicDepositTransactionDTO
     */
    public function setBookingDayOfMonth(int $bookingDayOfMonth): PeriodicDepositTransactionDTO
    {
        $this->bookingDayOfMonth = $bookingDayOfMonth;
        return $this;
    }

    /**
     * @return int
     */
    public function getBookingPeriodOffset(): int
    {
        return $this->bookingPeriodOffset;
    }

    /**
     * @param int $bookingPeriodOffset
     * @return PeriodicDepositTransactionDTO
     */
    public function setBookingPeriodOffset(int $bookingPeriodOffset): PeriodicDepositTransactionDTO
    {
        $this->bookingPeriodOffset = $bookingPeriodOffset;
        return $this;
    }
}
