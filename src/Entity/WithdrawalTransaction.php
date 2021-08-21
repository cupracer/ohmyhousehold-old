<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Transaction\WithdrawalTransactionRepository;

/**
 * @ORM\Entity(repositoryClass=WithdrawalTransactionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class WithdrawalTransaction extends Transaction
{
    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="withdrawalTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private AssetAccount $source;

    /**
     * @ORM\ManyToOne(targetEntity=ExpenseAccount::class, inversedBy="withdrawalTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private ExpenseAccount $destination;

    /**
     * @ORM\ManyToOne(targetEntity=HouseholdUser::class, inversedBy="withdrawalTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $householdUser;

    /**
     * @ORM\ManyToOne(targetEntity=BookingCategory::class, inversedBy="withdrawalTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $bookingCategory;

    /**
     * @ORM\ManyToOne(targetEntity=PeriodicWithdrawalTransaction::class, inversedBy="withdrawalTransactions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $periodicWithdrawalTransaction;

    /**
     * @return AssetAccount
     */
    public function getSource(): AssetAccount
    {
        return $this->source;
    }

    /**
     * @param AssetAccount|null $source
     */
    public function setSource(?AssetAccount $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return ExpenseAccount
     */
    public function getDestination(): ExpenseAccount
    {
        return $this->destination;
    }

    /**
     * @param ExpenseAccount|null $destination
     */
    public function setDestination(?ExpenseAccount $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getHouseholdUser(): ?HouseholdUser
    {
        return $this->householdUser;
    }

    public function setHouseholdUser(?HouseholdUser $householdUser): self
    {
        $this->householdUser = $householdUser;

        return $this;
    }

    public function getBookingCategory(): ?BookingCategory
    {
        return $this->bookingCategory;
    }

    public function setBookingCategory(?BookingCategory $bookingCategory): self
    {
        $this->bookingCategory = $bookingCategory;

        return $this;
    }

    public function getPeriodicWithdrawalTransaction(): ?PeriodicWithdrawalTransaction
    {
        return $this->periodicWithdrawalTransaction;
    }

    public function setPeriodicWithdrawalTransaction(?PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): self
    {
        $this->periodicWithdrawalTransaction = $periodicWithdrawalTransaction;

        return $this;
    }
}
