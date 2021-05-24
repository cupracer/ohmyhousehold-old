<?php

namespace App\Entity\DTO;

use App\Entity\AccountHolder;
use App\Entity\BookingCategory;
use Symfony\Component\Validator\Constraints as Assert;

class Booking
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
     * @return Booking
     */
    public function setBookingDate(\DateTimeInterface $bookingDate): Booking
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
     * @return Booking
     */
    public function setBookingCategory(BookingCategory $bookingCategory): Booking
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
     * @return Booking
     */
    public function setAccountHolder(AccountHolder $accountHolder): Booking
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
     * @return Booking
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
     * @return Booking
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
     * @return Booking
     */
    public function setPrivate($private)
    {
        $this->private = $private;
        return $this;
    }
}
