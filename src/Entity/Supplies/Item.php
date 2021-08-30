<?php

namespace App\Entity\Supplies;

use App\Entity\Household;
use App\Repository\Supplies\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Item
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="supplyItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $purchaseDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $bestBeforeDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $withdrawalDate;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(\DateTimeInterface $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    public function getBestBeforeDate(): ?\DateTimeInterface
    {
        return $this->bestBeforeDate;
    }

    public function setBestBeforeDate(?\DateTimeInterface $bestBeforeDate): self
    {
        $this->bestBeforeDate = $bestBeforeDate;

        return $this;
    }

    public function getWithdrawalDate(): ?\DateTimeInterface
    {
        return $this->withdrawalDate;
    }

    public function setWithdrawalDate(?\DateTimeInterface $withdrawalDate): self
    {
        $this->withdrawalDate = $withdrawalDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
