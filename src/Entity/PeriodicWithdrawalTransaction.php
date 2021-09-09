<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PeriodicTransaction\PeriodicWithdrawalTransactionRepository;

/**
 * @ORM\Entity(repositoryClass=PeriodicWithdrawalTransactionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class PeriodicWithdrawalTransaction extends PeriodicTransaction
{
    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="periodicWithdrawalTransactions")
     */
    private ?AssetAccount $source;

    /**
     * @ORM\ManyToOne(targetEntity=ExpenseAccount::class, inversedBy="periodicWithdrawalTransactions")
     */
    private ?ExpenseAccount $destination;

    /**
     * @ORM\ManyToOne(targetEntity=HouseholdUser::class, inversedBy="periodicWithdrawalTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $householdUser;

    /**
     * @ORM\ManyToOne(targetEntity=BookingCategory::class, inversedBy="periodicWithdrawalTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $bookingCategory;

    /**
     * @ORM\OneToMany(targetEntity=WithdrawalTransaction::class, mappedBy="periodicWithdrawalTransaction")
     */
    private $withdrawalTransactions;

    public function __construct()
    {
        $this->withdrawalTransactions = new ArrayCollection();
    }

    /**
     * @return AssetAccount|null
     */
    public function getSource(): ?AssetAccount
    {
        return $this->source;
    }

    /**
     * @param AssetAccount|null $source
     */
    public function setSource(?AssetAccount $source): void
    {
        $this->source = $source;
    }

    /**
     * @return ExpenseAccount|null
     */
    public function getDestination(): ?ExpenseAccount
    {
        return $this->destination;
    }

    /**
     * @param ExpenseAccount|null $destination
     */
    public function setDestination(?ExpenseAccount $destination): void
    {
        $this->destination = $destination;
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

    /**
     * @return Collection
     */
    public function getWithdrawalTransactions(): Collection
    {
        return $this->withdrawalTransactions;
    }

    public function addWithdrawalTransaction(WithdrawalTransaction $withdrawalTransaction): self
    {
        if (!$this->withdrawalTransactions->contains($withdrawalTransaction)) {
            $this->withdrawalTransactions[] = $withdrawalTransaction;
            $withdrawalTransaction->setPeriodicWithdrawalTransaction($this);
        }

        return $this;
    }

    public function removeWithdrawalTransaction(WithdrawalTransaction $withdrawalTransaction): self
    {
        if ($this->withdrawalTransactions->removeElement($withdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($withdrawalTransaction->getPeriodicWithdrawalTransaction() === $this) {
                $withdrawalTransaction->setPeriodicWithdrawalTransaction(null);
            }
        }

        return $this;
    }
}
