<?php

namespace App\Entity;

use App\Repository\Account\RevenueAccountRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RevenueAccountRepository::class)
 */
class RevenueAccount extends BaseAccount
{
    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="revenueAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\ManyToOne(targetEntity=AccountHolder::class, inversedBy="revenueAccounts")
     */
    private $accountHolder;

    /**
     * @ORM\OneToMany(targetEntity=DepositTransaction::class, mappedBy="source", orphanRemoval=true)
     */
    private $depositTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicDepositTransaction::class, mappedBy="source", orphanRemoval=true)
     */
    private $periodicDepositTransactions;


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
    public function getDepositTransactions(): Collection
    {
        return $this->depositTransactions;
    }

    public function addDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if (!$this->depositTransactions->contains($depositTransaction)) {
            $this->depositTransactions[] = $depositTransaction;
            $depositTransaction->setSource($this);
        }

        return $this;
    }

    public function removeDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if ($this->depositTransactions->removeElement($depositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($depositTransaction->getSource() === $this) {
                $depositTransaction->setSource(null);
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
    public function getPeriodicDepositTransactions(): Collection
    {
        return $this->periodicDepositTransactions;
    }

    public function addPeriodicDepositTransaction(PeriodicDepositTransaction $periodicDepositTransaction): self
    {
        if (!$this->periodicDepositTransactions->contains($periodicDepositTransaction)) {
            $this->periodicDepositTransactions[] = $periodicDepositTransaction;
            $periodicDepositTransaction->setSource($this);
        }

        return $this;
    }

    public function removePeriodicDepositTransaction(PeriodicDepositTransaction $periodicDepositTransaction): self
    {
        if ($this->periodicDepositTransactions->removeElement($periodicDepositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicDepositTransaction->getSource() === $this) {
                $periodicDepositTransaction->setSource(null);
            }
        }

        return $this;
    }
}
