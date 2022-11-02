<?php

namespace App\Entity\Supplies\DTO;

use App\Entity\Supplies\Product;
use App\Entity\Supplies\StorageLocation;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ItemEditDTO
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
     * @var StorageLocation
     */
    private $storageLocation;

    /**
     * @return DateTimeInterface|null
     */
    public function getPurchaseDate(): ?DateTimeInterface
    {
        return $this->purchaseDate;
    }

    /**
     * @param DateTimeInterface|null $purchaseDate
     * @return ItemEditDTO
     */
    public function setPurchaseDate(?DateTimeInterface $purchaseDate): ItemEditDTO
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
     * @return ItemEditDTO
     */
    public function setProduct(?Product $product): ItemEditDTO
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
     * @return ItemEditDTO
     */
    public function setBestBeforeDate(?DateTimeInterface $bestBeforeDate): ItemEditDTO
    {
        $this->bestBeforeDate = $bestBeforeDate;
        return $this;
    }

    /**
     * @return StorageLocation|null
     */
    public function getStorageLocation(): ?StorageLocation
    {
        return $this->storageLocation;
    }

    /**
     * @param StorageLocation|null $storageLocation
     * @return ItemEditDTO
     */
    public function setStorageLocation(?StorageLocation $storageLocation): ItemEditDTO
    {
        $this->storageLocation = $storageLocation;
        return $this;
    }
}
