<?php

namespace App\Entity\DTO;

use App\Entity\AssetAccount;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TransferTransactionDTO
{
    /**
     * @var DateTimeInterface
     */
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private $bookingDate;

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
     * @return TransferTransactionDTO
     */
    public function setBookingDate(DateTimeInterface $bookingDate): TransferTransactionDTO
    {
        $this->bookingDate = $bookingDate;
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
     * @return TransferTransactionDTO
     */
    public function setSource(AssetAccount $source): TransferTransactionDTO
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
     * @return TransferTransactionDTO
     */
    public function setDestination(AssetAccount $destination): TransferTransactionDTO
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
     * @return TransferTransactionDTO
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
     * @return TransferTransactionDTO
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
     * @return TransferTransactionDTO
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
     * @return TransferTransactionDTO
     */
    public function setBookingPeriodOffset(int $bookingPeriodOffset): TransferTransactionDTO
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
     * @return TransferTransactionDTO
     */
    public function setCompleted(bool $completed): TransferTransactionDTO
    {
        $this->completed = $completed;
        return $this;
    }
}
