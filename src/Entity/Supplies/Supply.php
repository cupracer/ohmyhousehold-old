<?php

namespace App\Entity\Supplies;

use App\Entity\Household;
use App\Repository\Supplies\SupplyRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=SupplyRepository::class)
 * @ORM\Table(name="supplies_supply")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"name", "household"},
 *     errorPath="name",
 *     message="This name is already in use in this household."
 *     )
 * @UniqueEntity(
 *     fields={"name", "category"},
 *     errorPath="name",
 *     message="This name already exists for the chosen category."
 *     )
 */
class Supply
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
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="supplies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="supplies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $minimumNumber;

    /**
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="supply")
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new DateTimeImmutable();
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getMinimumNumber(): ?int
    {
        return $this->minimumNumber;
    }

    public function setMinimumNumber(?int $minimumNumber): self
    {
        $this->minimumNumber = $minimumNumber;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setSupply($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getSupply() === $this) {
                $product->setSupply(null);
            }
        }

        return $this;
    }
}
