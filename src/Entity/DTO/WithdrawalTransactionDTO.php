<?php

namespace App\Entity\DTO;

use App\Entity\AccountHolder;
use App\Entity\AssetAccount;
use App\Entity\BookingCategory;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
     */
    public function setBookingDate(DateTimeInterface $bookingDate): WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
     */
    public function setBookingCategory(BookingCategory $bookingCategory): WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
     */
    public function setSource(AssetAccount $source): WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
     */
    public function setDestination(AccountHolder $destination): WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
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
     * @return WithdrawalTransactionDTO
     */
    public function setBookingPeriodOffset(int $bookingPeriodOffset): WithdrawalTransactionDTO
    {
        $this->bookingPeriodOffset = $bookingPeriodOffset;
        return $this;
    }
}
