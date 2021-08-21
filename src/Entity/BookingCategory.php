<?php

namespace App\Entity;

use App\Repository\BookingCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=BookingCategoryRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"name", "household"},
 *     errorPath="name",
 *     message="This name is already in use in this household."
 *     )
 */
class BookingCategory implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="bookingCategories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\OneToMany(targetEntity=WithdrawalTransaction::class, mappedBy="bookingCategory", orphanRemoval=false)
     */
    private $withdrawalTransactions;

    /**
     * @ORM\OneToMany(targetEntity=DepositTransaction::class, mappedBy="bookingCategory", orphanRemoval=true)
     */
    private $depositTransactions;

    /**
     * @ORM\OneToMany(targetEntity=TransferTransaction::class, mappedBy="bookingCategory", orphanRemoval=true)
     */
    private $transferTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicDepositTransaction::class, mappedBy="bookingCategory", orphanRemoval=true)
     */
    private $periodicDepositTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicWithdrawalTransaction::class, mappedBy="bookingCategory", orphanRemoval=true)
     */
    private $periodicWithdrawalTransactions;

    /**
     * @ORM\OneToMany(targetEntity=PeriodicTransferTransaction::class, mappedBy="bookingCategory", orphanRemoval=true)
     */
    private $periodicTransferTransactions;


    public function __construct()
    {
        $this->withdrawalTransactions = new ArrayCollection();
        $this->depositTransactions = new ArrayCollection();
        $this->transferTransactions = new ArrayCollection();
        $this->periodicDepositTransactions = new ArrayCollection();
        $this->periodicWithdrawalTransactions = new ArrayCollection();
        $this->periodicTransferTransactions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function jsonSerialize()
    {
        return [
            "name" => $this->name,
            "createdAt" => $this->createdAt,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
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
    public function getWithdrawalTransactions(): Collection
    {
        return $this->withdrawalTransactions;
    }

    public function addWithdrawalTransaction(WithdrawalTransaction $withdrawalTransaction): self
    {
        if (!$this->withdrawalTransactions->contains($withdrawalTransaction)) {
            $this->withdrawalTransactions[] = $withdrawalTransaction;
            $withdrawalTransaction->setBookingCategory($this);
        }

        return $this;
    }

    public function removeWithdrawalTransaction(WithdrawalTransaction $withdrawalTransaction): self
    {
        if ($this->withdrawalTransactions->removeElement($withdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($withdrawalTransaction->getBookingCategory() === $this) {
                $withdrawalTransaction->setBookingCategory(null);
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
            $depositTransaction->setBookingCategory($this);
        }

        return $this;
    }

    public function removeDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if ($this->depositTransactions->removeElement($depositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($depositTransaction->getBookingCategory() === $this) {
                $depositTransaction->setBookingCategory(null);
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
            $transferTransaction->setBookingCategory($this);
        }

        return $this;
    }

    public function removeTransferTransaction(TransferTransaction $transferTransaction): self
    {
        if ($this->transferTransactions->removeElement($transferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($transferTransaction->getBookingCategory() === $this) {
                $transferTransaction->setBookingCategory(null);
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
            $periodicDepositTransaction->setBookingCategory($this);
        }

        return $this;
    }

    public function removePeriodicDepositTransaction(PeriodicDepositTransaction $periodicDepositTransaction): self
    {
        if ($this->periodicDepositTransactions->removeElement($periodicDepositTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicDepositTransaction->getBookingCategory() === $this) {
                $periodicDepositTransaction->setBookingCategory(null);
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
            $periodicWithdrawalTransaction->setBookingCategory($this);
        }

        return $this;
    }

    public function removePeriodicWithdrawalTransaction(PeriodicWithdrawalTransaction $periodicWithdrawalTransaction): self
    {
        if ($this->periodicWithdrawalTransactions->removeElement($periodicWithdrawalTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicWithdrawalTransaction->getBookingCategory() === $this) {
                $periodicWithdrawalTransaction->setBookingCategory(null);
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
            $periodicTransferTransaction->setBookingCategory($this);
        }

        return $this;
    }

    public function removePeriodicTransferTransaction(PeriodicTransferTransaction $periodicTransferTransaction): self
    {
        if ($this->periodicTransferTransactions->removeElement($periodicTransferTransaction)) {
            // set the owning side to null (unless already changed)
            if ($periodicTransferTransaction->getBookingCategory() === $this) {
                $periodicTransferTransaction->setBookingCategory(null);
            }
        }

        return $this;
    }
}
