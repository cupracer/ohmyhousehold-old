<?php

namespace App\Entity\DTO;

use App\Entity\AccountHolder;
use App\Entity\BookingCategory;
use Symfony\Component\Validator\Constraints as Assert;

class DynamicBookingDTO
{
    /**
     * @var \DateTimeInterface
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
    private $accountHolder;

    #[Assert\NotBlank]
    private $amount;

    private $description;

    private $private;

    /**
     * @return \DateTimeInterface
     */
    public function getBookingDate(): \DateTimeInterface
    {
        return $this->bookingDate;
    }

    /**
     * @param \DateTimeInterface $bookingDate
     * @return DynamicBookingDTO
     */
    public function setBookingDate(\DateTimeInterface $bookingDate): DynamicBookingDTO
    {
        $this->bookingDate = $bookingDate;
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
     * @return DynamicBookingDTO
     */
    public function setBookingCategory(BookingCategory $bookingCategory): DynamicBookingDTO
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
     * @return DynamicBookingDTO
     */
    public function setAccountHolder(AccountHolder $accountHolder): DynamicBookingDTO
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
     * @return DynamicBookingDTO
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
     * @return DynamicBookingDTO
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
     * @return DynamicBookingDTO
     */
    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }
}
