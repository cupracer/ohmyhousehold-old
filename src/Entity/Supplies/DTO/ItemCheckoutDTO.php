<?php

namespace App\Entity\Supplies\DTO;

use App\Entity\Supplies\Product;
use Symfony\Component\Validator\Constraints as Assert;

class ItemCheckoutDTO
{
    /**
     * @var Product
     */
    #[Assert\NotBlank]
    private $product;

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param Product|null $product
     * @return ItemCheckoutDTO
     */
    public function setProduct(?Product $product): ItemCheckoutDTO
    {
        $this->product = $product;
        return $this;
    }
}
