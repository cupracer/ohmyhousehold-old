<?php

namespace App\Entity;

use App\Repository\Account\AssetAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AssetAccountRepository::class)
 */
class AssetAccount extends BaseAccount
{
    public const TYPE_CURRENT = 'current';
    public const TYPE_SAVINGS = 'savings';
    public const TYPE_PREPAID = 'prepaid';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="assetAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\ManyToMany(targetEntity=HouseholdUser::class, inversedBy="assetAccounts")
     */
    private $owners;

    /**
     * @ORM\OneToMany(targetEntity=WithdrawalTransaction::class, mappedBy="source")
     */
    private $withdrawalTransactions;

    /**
     * @ORM\OneToMany(targetEntity=DepositTransaction::class, mappedBy="destination")
     */
    private $depositTransactions;

    /**
     * @ORM\OneToMany(targetEntity=TransferTransaction::class, mappedBy="source")
     */
    private $outgoingTransferTransactions;

    /**
     * @ORM\OneToMany(targetEntity=TransferTransaction::class, mappedBy="destination")
     */
    private $incomingTransferTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicDepositTransaction::class, mappedBy="destination")
     */
    private $periodicDepositTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicWithdrawalTransaction::class, mappedBy="source")
     */
    private $periodicWithdrawalTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicTransferTransaction::class, mappedBy="source")
     */
    private $outgoingPeriodicTransferTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicTransferTransaction::class, mappedBy="destination")
     */
    private $incomingPeriodicTransferTransactions;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $accountType;

    public function __construct()
    {
        $this->owners = new ArrayCollection();
        $this->withdrawalTransactions = new ArrayCollection();
        $this->depositTransactions = new ArrayCollection();
        $this->incomingTransferTransactions = new ArrayCollection();
        $this->outgoingTransferTransactions = new ArrayCollection();
        $this->periodicDepositTransactions = new ArrayCollection();
        $this->periodicWithdrawalTransactions = new ArrayCollection();
        $this->incomingPeriodicTransferTransactions = new ArrayCollection();
        $this->outgoingPeriodicTransferTransactions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            "name" => $this->name,
            "owners" => $this->owners,
            "accountType" => $this->accountType,
        ]);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOwners(): Collection
    {
        return $this->owners;
    }

    public function addOwner(HouseholdUser $owner): self
    {
        if (!$this->owners->contains($owner)) {
            $this->owners[] = $owner;
        }

        return $this;
    }

    public function removeOwner(HouseholdUser $owner): self
    {
        $this->owners->removeElement($owner);

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
            $withdrawalTransaction->setSource($this);
        }

        return $this;
    }

    public function removeWithdrawalTransaction(WithdrawalTransaction $withdrawalTransaction): self
    {
        if ($this->withdrawalTransactions->removeElement($withdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($withdrawalTransaction->getSource() === $this) {
                $withdrawalTransaction->setSource(null);
            }
        }

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
            $depositTransaction->setDestination($this);
        }

        return $this;
    }

    public function removeDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if ($this->depositTransactions->removeElement($depositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($depositTransaction->getDestination() === $this) {
                $depositTransaction->setDestination(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOutgoingTransferTransactions(): Collection
    {
        return $this->outgoingTransferTransactions;
    }

    public function addOutgoingTransferTransaction(TransferTransaction $outgoingTransferTransaction): self
    {
        if (!$this->outgoingTransferTransactions->contains($outgoingTransferTransaction)) {
            $this->outgoingTransferTransactions[] = $outgoingTransferTransaction;
            $outgoingTransferTransaction->setSource($this);
        }

        return $this;
    }

    public function removeOutgoingTransferTransaction(TransferTransaction $outgoingTransferTransaction): self
    {
        if ($this->outgoingTransferTransactions->removeElement($outgoingTransferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($outgoingTransferTransaction->getSource() === $this) {
                $outgoingTransferTransaction->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getIncomingTransferTransactions(): Collection
    {
        return $this->incomingTransferTransactions;
    }

    public function addIncomingTransferTransaction(TransferTransaction $incomingTransferTransaction): self
    {
        if (!$this->incomingTransferTransactions->contains($incomingTransferTransaction)) {
            $this->incomingTransferTransactions[] = $incomingTransferTransaction;
            $incomingTransferTransaction->setSource($this);
        }

        return $this;
    }

    public function removeIncomingTransferTransaction(TransferTransaction $incomingTransferTransaction): self
    {
        if ($this->incomingTransferTransactions->removeElement($incomingTransferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($incomingTransferTransaction->getSource() === $this) {
                $incomingTransferTransaction->setSource(null);
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
            $periodicDepositTransaction->setDestination($this);
        }

        return $this;
    }

    public function removePeriodicDepositTransaction(PeriodicDepositTransaction $periodicDepositTransaction): self
    {
        if ($this->periodicDepositTransactions->removeElement($periodicDepositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicDepositTransaction->getDestination() === $this) {
                $periodicDepositTransaction->setDestination(null);
            }
        }

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
            $periodicWithdrawalTransaction->setSource($this);
        }

        return $this;
    }

    public function removePeriodicWithdrawalTransaction(PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): self
    {
        if ($this->periodicWithdrawalTransactions->removeElement($periodicWithdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicWithdrawalTransaction->getSource() === $this) {
                $periodicWithdrawalTransaction->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getOutgoingPeriodicTransferTransactions(): Collection
    {
        return $this->outgoingPeriodicTransferTransactions;
    }

    public function addOutgoingPeriodicTransferTransaction(PeriodicTransferTransaction $outgoingPeriodicTransferTransaction): self
    {
        if (!$this->outgoingPeriodicTransferTransactions->contains($outgoingPeriodicTransferTransaction)) {
            $this->outgoingPeriodicTransferTransactions[] = $outgoingPeriodicTransferTransaction;
            $outgoingPeriodicTransferTransaction->setSource($this);
        }

        return $this;
    }

    public function removeOutgoingPeriodicTransferTransaction(PeriodicTransferTransaction $outgoingPeriodicTransferTransaction): self
    {
        if ($this->outgoingPeriodicTransferTransactions->removeElement($outgoingPeriodicTransferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($outgoingPeriodicTransferTransaction->getSource() === $this) {
                $outgoingPeriodicTransferTransaction->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getIncomingPeriodicTransferTransactions(): Collection
    {
        return $this->incomingPeriodicTransferTransactions;
    }

    public function addIncomingPeriodicTransferTransaction(PeriodicTransferTransaction $incomingPeriodicTransferTransaction): self
    {
        if (!$this->incomingPeriodicTransferTransactions->contains($incomingPeriodicTransferTransaction)) {
            $this->incomingPeriodicTransferTransactions[] = $incomingPeriodicTransferTransaction;
            $incomingPeriodicTransferTransaction->setSource($this);
        }

        return $this;
    }

    public function removeIncomingPeriodicTransferTransaction(PeriodicTransferTransaction $incomingPeriodicTransferTransaction): self
    {
        if ($this->incomingPeriodicTransferTransactions->removeElement($incomingPeriodicTransferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($incomingPeriodicTransferTransaction->getSource() === $this) {
                $incomingPeriodicTransferTransaction->setSource(null);
            }
        }

        return $this;
    }

    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    public function setAccountType(?string $accountType): self
    {
        $this->accountType = $accountType;

        return $this;
    }
}
