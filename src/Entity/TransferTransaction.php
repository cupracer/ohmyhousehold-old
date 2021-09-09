<?php

namespace App\Entity;

use App\Repository\Transaction\TransferTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TransferTransactionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class TransferTransaction extends Transaction
{
    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="outgoingTransferTransactions")
     */
    private ?AssetAccount $source;

    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="incomingTransferTransactions")
     */
    private ?AssetAccount $destination;

    /**
     * @ORM\ManyToOne(targetEntity=HouseholdUser::class, inversedBy="transferTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $householdUser;

    /**
     * @ORM\ManyToOne(targetEntity=PeriodicTransferTransaction::class, inversedBy="transferTransactions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $periodicTransferTransaction;

    /**
     * @return AssetAccount|null
     */
    public function getSource(): ?AssetAccount
    {
        return $this->source;
    }

    /**
     * @param AssetAccount|null $source
     * @return TransferTransaction
     */
    public function setSource(?AssetAccount $source): self
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
     * @param AssetAccount|null $destination
     * @return TransferTransaction
     */
    public function setDestination(?AssetAccount $destination): self
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

    public function getPeriodicTransferTransaction(): ?PeriodicTransferTransaction
    {
        return $this->periodicTransferTransaction;
    }

    public function setPeriodicTransferTransaction(?PeriodicTransferTransaction $periodicTransferTransaction): self
    {
        $this->periodicTransferTransaction = $periodicTransferTransaction;

        return $this;
    }
}
