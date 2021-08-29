<?php

namespace App\Entity\Supplies\DTO;

use App\Entity\Supplies\Brand;
use App\Entity\Supplies\Measure;
use App\Entity\Supplies\Packaging;
use App\Entity\Supplies\Supply;
use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    /**
     * @var Supply
     */
    #[Assert\NotBlank]
    private $supply;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Brand
     */
    #[Assert\NotBlank]
    private $brand;

    /**
     * @var int
     */
    #[Assert\Type(type: ['integer', 'null'])]
    private $ean;

    /**
     * @var Measure
     */
    #[Assert\NotBlank]
    private $measure;

    /**
     * @var string
     */
    #[Assert\Type(type: 'numeric')]
    private $quantity;

    /**
     * @var bool
     */
    #[Assert\Type(type: ['bool', 'null'])]
    private $organicCertification;

    /**
     * @var Packaging
     */
    #[Assert\NotBlank]
    private $packaging;

    /**
     * @var int
     */
    #[Assert\Type(type: ['integer', 'null'])]
    private $minimumNumber;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return ProductDTO
     */
    public function setName(?string $name): ProductDTO
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Supply|null
     */
    public function getSupply(): ?Supply
    {
        return $this->supply;
    }

    /**
     * @param Supply|null $supply
     * @return ProductDTO
     */
    public function setSupply(?Supply $supply): ProductDTO
    {
        $this->supply = $supply;
        return $this;
    }

    /**
     * @return Brand|null
     */
    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand|null $brand
     * @return ProductDTO
     */
    public function setBrand(?Brand $brand): ProductDTO
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEan(): ?int
    {
        return $this->ean;
    }

    /**
     * @param int|null $ean
     * @return ProductDTO
     */
    public function setEan(?int $ean): ProductDTO
    {
        $this->ean = $ean;
        return $this;
    }

    /**
     * @return Measure|null
     */
    public function getMeasure(): ?Measure
    {
        return $this->measure;
    }

    /**
     * @param Measure|null $measure
     * @return ProductDTO
     */
    public function setMeasure(?Measure $measure): ProductDTO
    {
        $this->measure = $measure;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     * @return ProductDTO
     */
    public function setQuantity(string $quantity): ProductDTO
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return bool
     */
    public function getOrganicCertification(): bool
    {
        return $this->organicCertification;
    }

    /**
     * @param bool $organicCertification
     * @return ProductDTO
     */
    public function setOrganicCertification(bool $organicCertification): ProductDTO
    {
        $this->organicCertification = $organicCertification;
        return $this;
    }

    /**
     * @return Packaging|null
     */
    public function getPackaging(): ?Packaging
    {
        return $this->packaging;
    }

    /**
     * @param Packaging|null $packaging
     * @return ProductDTO
     */
    public function setPackaging(?Packaging $packaging): ProductDTO
    {
        $this->packaging = $packaging;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinimumNumber(): ?int
    {
        return $this->minimumNumber;
    }

    /**
     * @param int|null $minimumNumber
     * @return ProductDTO
     */
    public function setMinimumNumber(?int $minimumNumber): ProductDTO
    {
        $this->minimumNumber = $minimumNumber;
        return $this;
    }
}
