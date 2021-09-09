<?php

namespace App\Entity\Supplies\DTO;

use App\Entity\Supplies\Product;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ItemDTO
{
    /**
     * @var DateTimeInterface
     */
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private $purchaseDate;

    /**
     * @var Product
     */
    #[Assert\NotBlank]
    private $product;

    /**
     * @var DateTimeInterface
     */
    #[Assert\Type("\DateTimeInterface")]
    private $bestBeforeDate;

    /**
     * @var int
     */
    #[Assert\Type(type: 'integer')]
    private $quantity;

    public function __construct()
    {
        $this->quantity = 1;
    }

    /**
     * @return DateTimeInterface
     */
    public function getPurchaseDate(): DateTimeInterface
    {
        return $this->purchaseDate;
    }

    /**
     * @param DateTimeInterface $purchaseDate
     * @return ItemDTO
     */
    public function setPurchaseDate(DateTimeInterface $purchaseDate): ItemDTO
    {
        $this->purchaseDate = $purchaseDate;
        return $this;
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     * @return ItemDTO
     */
    public function setProduct(?Product $product): ItemDTO
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getBestBeforeDate(): ?DateTimeInterface
    {
        return $this->bestBeforeDate;
    }

    /**
     * @param DateTimeInterface|null $bestBeforeDate
     * @return ItemDTO
     */
    public function setBestBeforeDate(?DateTimeInterface $bestBeforeDate): ItemDTO
    {
        $this->bestBeforeDate = $bestBeforeDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     * @return ItemDTO
     */
    public function setQuantity(int $quantity): ItemDTO
    {
        $this->quantity = $quantity;
        return $this;
    }
}
