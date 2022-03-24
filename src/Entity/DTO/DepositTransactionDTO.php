<?php

namespace App\Entity\DTO;

use App\Entity\AccountHolder;
use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DepositTransactionDTO
{
    /**
     * @var DateTimeInterface
     */
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private $bookingDate;

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

    private bool $completed;

    public function __construct()
    {
        $this->bookingPeriodOffset = 0;
    }

    /**
     * @return DateTimeInterface
     */
    public function getBookingDate(): DateTimeInterface
    {
        return $this->bookingDate;
    }

    /**
     * @param DateTimeInterface $bookingDate
     * @return DepositTransactionDTO
     */
    public function setBookingDate(DateTimeInterface $bookingDate): DepositTransactionDTO
    {
        $this->bookingDate = $bookingDate;
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
     * @return DepositTransactionDTO
     */
    public function setBookingCategory(BookingCategory $bookingCategory): DepositTransactionDTO
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
     * @return DepositTransactionDTO
     */
    public function setSource(AccountHolder $source): DepositTransactionDTO
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
     * @return DepositTransactionDTO
     */
    public function setDestination(AssetAccount $destination): DepositTransactionDTO
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
     * @return DepositTransactionDTO
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
     * @return DepositTransactionDTO
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
     * @return DepositTransactionDTO
     */
    public function setPrivate($private)
    {
        $this->private = $private;
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
     * @return DepositTransactionDTO
     */
    public function setBookingPeriodOffset(int $bookingPeriodOffset): DepositTransactionDTO
    {
        $this->bookingPeriodOffset = $bookingPeriodOffset;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     * @return DepositTransactionDTO
     */
    public function setCompleted(bool $completed): DepositTransactionDTO
    {
        $this->completed = $completed;
        return $this;
    }
}
