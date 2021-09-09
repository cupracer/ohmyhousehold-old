<?php

namespace App\Entity\DTO;

use App\Entity\AssetAccount;
use Symfony\Component\Validator\Constraints as Assert;

class PeriodicTransferTransactionDTO
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
     * @var AssetAccount
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
     * @return PeriodicTransferTransactionDTO
     */
    public function setStartDate(\DateTimeInterface $startDate): PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
     */
    public function setEndDate(?\DateTimeInterface $endDate): PeriodicTransferTransactionDTO
    {
        $this->endDate = $endDate;
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
     * @return PeriodicTransferTransactionDTO
     */
    public function setSource(AssetAccount $source): PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
     */
    public function setDestination(AssetAccount $destination): PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
     */
    public function setBookingInterval(int $bookingInterval): PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
     */
    public function setBookingDayOfMonth(int $bookingDayOfMonth): PeriodicTransferTransactionDTO
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
     * @return PeriodicTransferTransactionDTO
     */
    public function setBookingPeriodOffset(int $bookingPeriodOffset): PeriodicTransferTransactionDTO
    {
        $this->bookingPeriodOffset = $bookingPeriodOffset;
        return $this;
    }
}
