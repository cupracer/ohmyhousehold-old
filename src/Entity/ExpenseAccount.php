<?php

namespace App\Entity;

use App\Repository\Account\ExpenseAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseAccountRepository::class)
 */
class ExpenseAccount extends BaseAccount
{
    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="expenseAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\ManyToOne(targetEntity=AccountHolder::class, inversedBy="expenseAccounts")
     */
    private $accountHolder;

    /**
     * @ORM\OneToMany(targetEntity=WithdrawalTransaction::class, mappedBy="destination", orphanRemoval=true)
     */
    private $withdrawalTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicWithdrawalTransaction::class, mappedBy="destination", orphanRemoval=true)
     */
    private $periodicWithdrawalTransactions;


    public function __construct()
    {
        $this->withdrawalTransactions = new ArrayCollection();
        $this->periodicWithdrawalTransactions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->accountHolder->getName();
    }

    public function getAccountHolder(): ?AccountHolder
    {
        return $this->accountHolder;
    }

    public function setAccountHolder(?AccountHolder $accountHolder): self
    {
        $this->accountHolder = $accountHolder;

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
            $withdrawalTransaction->setDestination($this);
        }

        return $this;
    }

    public function removeWithdrawalTransaction(WithdrawalTransaction $withdrawalTransaction): self
    {
        if ($this->withdrawalTransactions->removeElement($withdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($withdrawalTransaction->getDestination() === $this) {
                $withdrawalTransaction->setDestination(null);
            }
        }

        return $this;
    }

    public function getHousehold(): ?Household
    {
        return $this->household;
    }

    public function setHousehold(?Household $household): self
    {
        $this->household = $household;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPeriodicWithdrawalTransactions(): Collection
    {
        return $this->periodicWithdrawalTransactions;
    }

    public function addPeriodicWithdrawalTransaction(PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): self
    {
        if (!$this->periodicWithdrawalTransactions->contains($periodicWithdrawalTransaction)) {
            $this->periodicWithdrawalTransactions[] = $periodicWithdrawalTransaction;
            $periodicWithdrawalTransaction->setDestination($this);
        }

        return $this;
    }

    public function removePeriodicWithdrawalTransaction(PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): self
    {
        if ($this->periodicWithdrawalTransactions->removeElement($periodicWithdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicWithdrawalTransaction->getDestination() === $this) {
                $periodicWithdrawalTransaction->setDestination(null);
            }
        }

        return $this;
    }
}
