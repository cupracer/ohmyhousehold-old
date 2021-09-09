<?php

namespace App\Entity;

use App\Entity\Supplies\Brand as SupplyBrand;
use App\Entity\Supplies\Category as SupplyCategory;
use App\Entity\Supplies\Item;
use App\Entity\Supplies\Measure as SupplyMeasure;
use App\Entity\Supplies\Packaging as SupplyPackaging;
use App\Entity\Supplies\Product as SupplyProduct;
use App\Entity\Supplies\Supply;
use App\Repository\HouseholdRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HouseholdRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Household
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=HouseholdUser::class, mappedBy="household", orphanRemoval=true)
     */
    private $householdUsers;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=AccountHolder::class, mappedBy="household", orphanRemoval=true)
     */
    private $accountHolders;

    /**
     * @ORM\OneToMany(targetEntity=BookingCategory::class, mappedBy="household", orphanRemoval=true)
     */
    private $bookingCategories;

    /**
     * @ORM\OneToMany(targetEntity=AssetAccount::class, mappedBy="household", orphanRemoval=true)
     */
    private $assetAccounts;

    /**
     * @ORM\OneToMany(targetEntity=ExpenseAccount::class, mappedBy="household", orphanRemoval=true)
     */
    private $expenseAccounts;

    /**
     * @ORM\OneToMany(targetEntity=RevenueAccount::class, mappedBy="household", orphanRemoval=true)
     */
    private $revenueAccounts;

    /**
     * @ORM\OneToMany(targetEntity=SupplyBrand::class, mappedBy="household")
     */
    private $brands;

    /**
     * @ORM\OneToMany(targetEntity=SupplyCategory::class, mappedBy="household")
     */
    private $supplyCategories;

    /**
     * @ORM\OneToMany(targetEntity=Supply::class, mappedBy="household")
     */
    private $supplies;

    /**
     * @ORM\OneToMany(targetEntity=SupplyPackaging::class, mappedBy="household")
     */
    private $supplyPackagings;

    /**
     * @ORM\OneToMany(targetEntity=SupplyMeasure::class, mappedBy="household")
     */
    private $supplyMeasures;

    /**
     * @ORM\OneToMany(targetEntity=SupplyProduct::class, mappedBy="household")
     */
    private $supplyProducts;

    /**
     * @ORM\OneToMany(targetEntity=Item::class, mappedBy="household")
     */
    private $supplyItems;

    public function __construct()
    {
        $this->householdUsers = new ArrayCollection();
        $this->accountHolders = new ArrayCollection();
        $this->bookingCategories = new ArrayCollection();
        $this->assetAccounts = new ArrayCollection();
        $this->expenseAccounts = new ArrayCollection();
        $this->revenueAccounts = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->supplyCategories = new ArrayCollection();
        $this->supplies = new ArrayCollection();
        $this->supplyPackagings = new ArrayCollection();
        $this->supplyMeasures = new ArrayCollection();
        $this->supplyProducts = new ArrayCollection();
        $this->supplyItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function getHouseholdUsers(): Collection
    {
        return $this->householdUsers;
    }

    public function addHouseholdUser(HouseholdUser $householdUser): self
    {
        if (!$this->householdUsers->contains($householdUser)) {
            $this->householdUsers[] = $householdUser;
            $householdUser->setHousehold($this);
        }

        return $this;
    }

    public function removeHouseholdUser(HouseholdUser $householdUser): self
    {
        if ($this->householdUsers->removeElement($householdUser)) {
            // set the owning side to null (unless already changed)
            if ($householdUser->getHousehold() === $this) {
                $householdUser->setHousehold(null);
            }
        }

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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
    public function getAccountHolders(): Collection
    {
        return $this->accountHolders;
    }

    public function addAccountHolder(AccountHolder $accountHolder): self
    {
        if (!$this->accountHolders->contains($accountHolder)) {
            $this->accountHolders[] = $accountHolder;
            $accountHolder->setHousehold($this);
        }

        return $this;
    }

    public function removeAccountHolder(AccountHolder $accountHolder): self
    {
        if ($this->accountHolders->removeElement($accountHolder)) {
            // set the owning side to null (unless already changed)
            if ($accountHolder->getHousehold() === $this) {
                $accountHolder->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getBookingCategories(): Collection
    {
        return $this->bookingCategories;
    }

    public function addBookingCategory(BookingCategory $bookingCategory): self
    {
        if (!$this->bookingCategories->contains($bookingCategory)) {
            $this->bookingCategories[] = $bookingCategory;
            $bookingCategory->setHousehold($this);
        }

        return $this;
    }

    public function removeBookingCategory(BookingCategory $bookingCategory): self
    {
        if ($this->bookingCategories->removeElement($bookingCategory)) {
            // set the owning side to null (unless already changed)
            if ($bookingCategory->getHousehold() === $this) {
                $bookingCategory->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplyBrands(): Collection
    {
        return $this->brands;
    }

    public function addSupplyBrand(SupplyBrand $brand): self
    {
        if (!$this->brands->contains($brand)) {
            $this->brands[] = $brand;
            $brand->setHousehold($this);
        }

        return $this;
    }

    public function removeSupplyBrand(SupplyBrand $brand): self
    {
        if ($this->brands->removeElement($brand)) {
            // set the owning side to null (unless already changed)
            if ($brand->getHousehold() === $this) {
                $brand->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplyCategories(): Collection
    {
        return $this->supplyCategories;
    }

    public function addSupplyCategory(SupplyCategory $supplyCategory): self
    {
        if (!$this->supplyCategories->contains($supplyCategory)) {
            $this->supplyCategories[] = $supplyCategory;
            $supplyCategory->setHousehold($this);
        }

        return $this;
    }

    public function removeSupplyCategory(SupplyCategory $supplyCategory): self
    {
        if ($this->supplyCategories->removeElement($supplyCategory)) {
            // set the owning side to null (unless already changed)
            if ($supplyCategory->getHousehold() === $this) {
                $supplyCategory->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplies(): Collection
    {
        return $this->supplies;
    }

    public function addSupply(Supply $supply): self
    {
        if (!$this->supplies->contains($supply)) {
            $this->supplies[] = $supply;
            $supply->setHousehold($this);
        }

        return $this;
    }

    public function removeSupply(Supply $supply): self
    {
        if ($this->supplies->removeElement($supply)) {
            // set the owning side to null (unless already changed)
            if ($supply->getHousehold() === $this) {
                $supply->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplyPackagings(): Collection
    {
        return $this->supplyPackagings;
    }

    public function addSupplyPackaging(SupplyPackaging $supplyPackaging): self
    {
        if (!$this->supplyPackagings->contains($supplyPackaging)) {
            $this->supplyPackagings[] = $supplyPackaging;
            $supplyPackaging->setHousehold($this);
        }

        return $this;
    }

    public function removeSupplyPackaging(SupplyPackaging $supplyPackaging): self
    {
        if ($this->supplyPackagings->removeElement($supplyPackaging)) {
            // set the owning side to null (unless already changed)
            if ($supplyPackaging->getHousehold() === $this) {
                $supplyPackaging->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplyMeasures(): Collection
    {
        return $this->supplyMeasures;
    }

    public function addSupplyMeasure(SupplyMeasure $supplyMeasure): self
    {
        if (!$this->supplyMeasures->contains($supplyMeasure)) {
            $this->supplyMeasures[] = $supplyMeasure;
            $supplyMeasure->setHousehold($this);
        }

        return $this;
    }

    public function removeSupplyMeasure(SupplyMeasure $supplyMeasure): self
    {
        if ($this->supplyMeasures->removeElement($supplyMeasure)) {
            // set the owning side to null (unless already changed)
            if ($supplyMeasure->getHousehold() === $this) {
                $supplyMeasure->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplyProducts(): Collection
    {
        return $this->supplyProducts;
    }

    public function addSupplyProduct(SupplyProduct $supplyProduct): self
    {
        if (!$this->supplyProducts->contains($supplyProduct)) {
            $this->supplyProducts[] = $supplyProduct;
            $supplyProduct->setHousehold($this);
        }

        return $this;
    }

    public function removeSupplyProduct(SupplyProduct $supplyProduct): self
    {
        if ($this->supplyProducts->removeElement($supplyProduct)) {
            // set the owning side to null (unless already changed)
            if ($supplyProduct->getHousehold() === $this) {
                $supplyProduct->setHousehold(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSupplyItems(): Collection
    {
        return $this->supplyItems;
    }

    public function addSupplyItem(Item $supplyItem): self
    {
        if (!$this->supplyItems->contains($supplyItem)) {
            $this->supplyItems[] = $supplyItem;
            $supplyItem->setHousehold($this);
        }

        return $this;
    }

    public function removeSupplyItem(Item $supplyItem): self
    {
        if ($this->supplyItems->removeElement($supplyItem)) {
            // set the owning side to null (unless already changed)
            if ($supplyItem->getHousehold() === $this) {
                $supplyItem->setHousehold(null);
            }
        }

        return $this;
    }
}
