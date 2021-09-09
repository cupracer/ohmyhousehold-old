<?php

namespace App\Entity;

use App\Repository\HouseholdUserRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HouseholdUserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class HouseholdUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="householdUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="householdUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdmin;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToMany(targetEntity=AssetAccount::class, mappedBy="owners")
     */
    private $assetAccounts;

    /**
     * @ORM\OneToMany(targetEntity=WithdrawalTransaction::class, mappedBy="householdUser", orphanRemoval=true)
     */
    private $withdrawalTransactions;

    /**
     * @ORM\OneToMany(targetEntity=DepositTransaction::class, mappedBy="householdUser", orphanRemoval=true)
     */
    private $depositTransactions;

    /**
     * @ORM\OneToMany(targetEntity=TransferTransaction::class, mappedBy="householdUser", orphanRemoval=true)
     */
    private $transferTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicDepositTransaction::class, mappedBy="householdUser", orphanRemoval=true)

     */
    private $periodicDepositTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicWithdrawalTransaction::class, mappedBy="householdUser", orphanRemoval=true)
     */
    private $periodicWithdrawalTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicTransferTransaction::class, mappedBy="householdUser", orphanRemoval=true)
     */
    private $periodicTransferTransactions;


    public function __construct()
    {
        $this->assetAccounts = new ArrayCollection();
        $this->withdrawalTransactions = new ArrayCollection();
        $this->depositTransactions = new ArrayCollection();
        $this->transferTransactions = new ArrayCollection();
        $this->periodicDepositTransactions = new ArrayCollection();
        $this->periodicWithdrawalTransactions = new ArrayCollection();
        $this->periodicTransferTransactions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->user->getUsername();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getIsAdmin(): ?bool
    {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist

     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return Collection
     */
    public function getAssetAccounts(): Collection
    {
        return $this->assetAccounts;
    }

    public function addAssetAccount(AssetAccount $assetAccount): self
    {
        if (!$this->assetAccounts->contains($assetAccount)) {
            $this->assetAccounts[] = $assetAccount;
            $assetAccount->addOwner($this);
        }

        return $this;
    }

    public function removeAssetAccount(AssetAccount $assetAccount): self
    {
        if ($this->assetAccounts->removeElement($assetAccount)) {
            $assetAccount->removeOwner($this);
        }

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
            $withdrawalTransaction->setHouseholdUser($this);
        }

        return $this;
    }

    public function removeWithdrawalTransaction(WithdrawalTransaction $withdrawalTransaction): self
    {
        if ($this->withdrawalTransactions->removeElement($withdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($withdrawalTransaction->getHouseholdUser() === $this) {
                $withdrawalTransaction->setHouseholdUser(null);
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
            $depositTransaction->setHouseholdUser($this);
        }

        return $this;
    }

    public function removeDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if ($this->depositTransactions->removeElement($depositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($depositTransaction->getHouseholdUser() === $this) {
                $depositTransaction->setHouseholdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTransferTransactions(): Collection
    {
        return $this->transferTransactions;
    }

    public function addTransferTransaction(TransferTransaction $transferTransaction): self
    {
        if (!$this->transferTransactions->contains($transferTransaction)) {
            $this->transferTransactions[] = $transferTransaction;
            $transferTransaction->setHouseholdUser($this);
        }

        return $this;
    }

    public function removeTransferTransaction(TransferTransaction $transferTransaction): self
    {
        if ($this->transferTransactions->removeElement($transferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($transferTransaction->getHouseholdUser() === $this) {
                $transferTransaction->setHouseholdUser(null);
            }
        }

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
            $periodicDepositTransaction->setHouseholdUser($this);
        }

        return $this;
    }

    public function removePeriodicDepositTransaction(PeriodicDepositTransaction $periodicDepositTransaction): self
    {
        if ($this->periodicDepositTransactions->removeElement($periodicDepositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicDepositTransaction->getHouseholdUser() === $this) {
                $periodicDepositTransaction->setHouseholdUser(null);
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
            $periodicWithdrawalTransaction->setHouseholdUser($this);
        }

        return $this;
    }

    public function removePeriodicWithdrawalTransaction(PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): self
    {
        if ($this->periodicWithdrawalTransactions->removeElement($periodicWithdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicWithdrawalTransaction->getHouseholdUser() === $this) {
                $periodicWithdrawalTransaction->setHouseholdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPeriodicTransferTransactions(): Collection
    {
        return $this->periodicTransferTransactions;
    }

    public function addPeriodicTransferTransaction(PeriodicTransferTransaction $periodicTransferTransaction): self
    {
        if (!$this->periodicTransferTransactions->contains($periodicTransferTransaction)) {
            $this->periodicTransferTransactions[] = $periodicTransferTransaction;
            $periodicTransferTransaction->setHouseholdUser($this);
        }

        return $this;
    }

    public function removePeriodicTransferTransaction(PeriodicTransferTransaction $periodicTransferTransaction): self
    {
        if ($this->periodicTransferTransactions->removeElement($periodicTransferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicTransferTransaction->getHouseholdUser() === $this) {
                $periodicTransferTransaction->setHouseholdUser(null);
            }
        }

        return $this;
    }
}
