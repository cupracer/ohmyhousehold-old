<?php

namespace App\Entity;

use App\Repository\AccountHolderRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=AccountHolderRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"name", "household"},
 *     errorPath="name",
 *     message="This name is already in use in this household."
 *     )
 */
class AccountHolder implements JsonSerializable
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
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="accountHolders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\OneToMany(targetEntity=ExpenseAccount::class, mappedBy="accountHolder")
     */
    private $expenseAccounts;

    /**
     * @ORM\OneToMany(targetEntity=RevenueAccount::class, mappedBy="accountHolder")
     */
    private $revenueAccounts;

    public function __construct()
    {
        $this->expenseAccounts = new ArrayCollection();
        $this->revenueAccounts = new ArrayCollection();
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
    public function getExpenseAccounts(): Collection
    {
        return $this->expenseAccounts;
    }

    public function addExpenseAccount(ExpenseAccount $expenseAccount): self
    {
        if (!$this->expenseAccounts->contains($expenseAccount)) {
            $this->expenseAccounts[] = $expenseAccount;
            $expenseAccount->setAccountHolder($this);
        }

        return $this;
    }

    public function removeExpenseAccount(ExpenseAccount $expenseAccount): self
    {
        if ($this->expenseAccounts->removeElement($expenseAccount)) {
            // set the owning side to null (unless already changed)
            if ($expenseAccount->getAccountHolder() === $this) {
                $expenseAccount->setAccountHolder(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection
     */
    public function getRevenueAccounts(): Collection
    {
        return $this->revenueAccounts;
    }

    public function addRevenueAccount(RevenueAccount $revenueAccount): self
    {
        if (!$this->revenueAccounts->contains($revenueAccount)) {
            $this->revenueAccounts[] = $revenueAccount;
            $revenueAccount->setAccountHolder($this);
        }

        return $this;
    }

    public function removeRevenueAccount(RevenueAccount $revenueAccount): self
    {
        if ($this->revenueAccounts->removeElement($revenueAccount)) {
            // set the owning side to null (unless already changed)
            if ($revenueAccount->getAccountHolder() === $this) {
                $revenueAccount->setAccountHolder(null);
            }
        }

        return $this;
    }
}
