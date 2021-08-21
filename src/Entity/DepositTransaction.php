<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Transaction\DepositTransactionRepository;

/**
 * @ORM\Entity(repositoryClass=DepositTransactionRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class DepositTransaction extends Transaction
{
    /**
     * @ORM\ManyToOne(targetEntity=RevenueAccount::class, inversedBy="depositTransactions")
     */
    private ?RevenueAccount $source;

    /**
     * @ORM\ManyToOne(targetEntity=AssetAccount::class, inversedBy="depositTransactions")
     */
    private ?AssetAccount $destination;

    /**
     * @ORM\ManyToOne(targetEntity=HouseholdUser::class, inversedBy="depositTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $householdUser;

    /**
     * @ORM\ManyToOne(targetEntity=BookingCategory::class, inversedBy="depositTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $bookingCategory;

    /**
     * @ORM\ManyToOne(targetEntity=PeriodicDepositTransaction::class, inversedBy="depositTransactions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $periodicDepositTransaction;

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
    public function setSource(?RevenueAccount $source): self
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

    public function getBookingCategory(): ?BookingCategory
    {
        return $this->bookingCategory;
    }

    public function setBookingCategory(?BookingCategory $bookingCategory): self
    {
        $this->bookingCategory = $bookingCategory;

        return $this;
    }

    public function getPeriodicDepositTransaction(): ?PeriodicDepositTransaction
    {
        return $this->periodicDepositTransaction;
    }

    public function setPeriodicDepositTransaction(?PeriodicDepositTransaction $periodicDepositTransaction): self
    {
        $this->periodicDepositTransaction = $periodicDepositTransaction;

        return $this;
    }
}
