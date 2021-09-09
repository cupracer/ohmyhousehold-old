<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PeriodicTransaction\PeriodicTransferTransactionRepository;

/**
 * @ORM\Entity(repositoryClass=PeriodicTransferTransactionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class PeriodicTransferTransaction extends PeriodicTransaction
{
    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="outgoingPeriodicTransferTransactions")
     */
    private ?AssetAccount $source;

    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="incomingPeriodicTransferTransactions")
     */
    private ?AssetAccount $destination;

    /**
     * @ORM\ManyToOne(targetEntity=HouseholdUser::class, inversedBy="periodicTransferTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $householdUser;

    /**
     * @ORM\OneToMany(targetEntity=TransferTransaction::class, mappedBy="periodicTransferTransaction")
     */
    private $transferTransactions;

    public function __construct()
    {
        $this->transferTransactions = new ArrayCollection();
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
     * @return AssetAccount|null
     */
    public function getDestination(): ?AssetAccount
    {
        return $this->destination;
    }

    /**
     * @param AssetAccount|null $destination
     */
    public function setDestination(?AssetAccount $destination): void
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

    /**
     * @return Collection|TransferTransaction[]
     */
    public function getTransferTransactions(): Collection
    {
        return $this->transferTransactions;
    }

    public function addTransferTransaction(TransferTransaction $transferTransaction): self
    {
        if (!$this->transferTransactions->contains($transferTransaction)) {
            $this->transferTransactions[] = $transferTransaction;
            $transferTransaction->setPeriodicTransferTransaction($this);
        }

        return $this;
    }

    public function removeTransferTransaction(TransferTransaction $transferTransaction): self
    {
        if ($this->transferTransactions->removeElement($transferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($transferTransaction->getPeriodicTransferTransaction() === $this) {
                $transferTransaction->setPeriodicTransferTransaction(null);
            }
        }

        return $this;
    }
}
