<?php

namespace App\Entity\DTO;

use App\Entity\AccountHolder;
use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PeriodicWithdrawalTransactionDTO
{
    /**
     * @var DateTimeInterface
     */
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private $startDate;

    /**
     * @var DateTimeInterface
     */
    #[Assert\Type("\DateTimeInterface")]
    private $endDate;

    /**
     * @var BookingCategory
     */
    #[Assert\NotBlank]
    private $bookingCategory;

    /**
     * @var AssetAccount
     */
    #[Assert\NotBlank]
    private $source;

    /**
     * @var AccountHolder
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
     * @return DateTimeInterface
     */
    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * @param DateTimeInterface $startDate
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setStartDate(DateTimeInterface $startDate): PeriodicWithdrawalTransactionDTO
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * @param DateTimeInterface|null $endDate
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setEndDate(?DateTimeInterface $endDate): PeriodicWithdrawalTransactionDTO
    {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return BookingCategory|null
     */
    public function getBookingCategory(): ?BookingCategory
    {
        return $this->bookingCategory;
    }

    /**
     * @param BookingCategory $bookingCategory
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setBookingCategory(BookingCategory $bookingCategory): PeriodicWithdrawalTransactionDTO
    {
        $this->bookingCategory = $bookingCategory;
        return $this;
    }

    /**
     * @return AssetAccount|null
     */
    public function getSource(): ?AssetAccount
    {
        return $this->source;
    }

    /**
     * @param AssetAccount $source
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setSource(AssetAccount $source): PeriodicWithdrawalTransactionDTO
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return AccountHolder|null
     */
    public function getDestination(): ?AccountHolder
    {
        return $this->destination;
    }

    /**
     * @param AccountHolder $destination
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setDestination(AccountHolder $destination): PeriodicWithdrawalTransactionDTO
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
     * @return PeriodicWithdrawalTransactionDTO
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
     * @return PeriodicWithdrawalTransactionDTO
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
     * @return PeriodicWithdrawalTransactionDTO
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
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setBookingInterval(int $bookingInterval): PeriodicWithdrawalTransactionDTO
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
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setBookingDayOfMonth(int $bookingDayOfMonth): PeriodicWithdrawalTransactionDTO
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
     * @return PeriodicWithdrawalTransactionDTO
     */
    public function setBookingPeriodOffset(int $bookingPeriodOffset): PeriodicWithdrawalTransactionDTO
    {
        $this->bookingPeriodOffset = $bookingPeriodOffset;
        return $this;
    }
}
