<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PeriodicTransaction\PeriodicDepositTransactionRepository;

/**
 * @ORM\Entity(repositoryClass=PeriodicDepositTransactionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class PeriodicDepositTransaction extends PeriodicTransaction
{
    /**
     * @ORM\ManyToOne(targetEntity=RevenueAccount::class, inversedBy="periodicDepositTransactions")
     */
    private ?RevenueAccount $source;

    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="periodicDepositTransactions")
     */
    private ?AssetAccount $destination;

    /**
     * @ORM\ManyToOne(targetEntity=HouseholdUser::class, inversedBy="periodicDepositTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $householdUser;

    /**
     * @ORM\ManyToOne(targetEntity=BookingCategory::class, inversedBy="periodicDepositTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $bookingCategory;

    /**
     * @ORM\OneToMany(targetEntity=DepositTransaction::class, mappedBy="periodicDepositTransaction")
     */
    private $depositTransactions;

    public function __construct()
    {
        $this->depositTransactions = new ArrayCollection();
    }

    /**
     * @return RevenueAccount|null
     */
    public function getSource(): ?RevenueAccount
    {
        return $this->source;
    }

    /**
     * @param RevenueAccount|null $source
     */
    public function setSource(?RevenueAccount $source): void
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
    public function getDepositTransactions(): Collection
    {
        return $this->depositTransactions;
    }

    public function addDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if (!$this->depositTransactions->contains($depositTransaction)) {
            $this->depositTransactions[] = $depositTransaction;
            $depositTransaction->setPeriodicDepositTransaction($this);
        }

        return $this;
    }

    public function removeDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if ($this->depositTransactions->removeElement($depositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($depositTransaction->getPeriodicDepositTransaction() === $this) {
                $depositTransaction->setPeriodicDepositTransaction(null);
            }
        }

        return $this;
    }
}
