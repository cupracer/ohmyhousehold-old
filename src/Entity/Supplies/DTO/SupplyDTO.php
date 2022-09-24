<?php

namespace App\Entity\Supplies\DTO;

use App\Entity\Supplies\Category;
use Symfony\Component\Validator\Constraints as Assert;

class SupplyDTO
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $name;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var int
     */
    #[Assert\Type(type: ['integer', 'null'])]
    private $minimumNumber;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SupplyDTO
     */
    public function setName(string $name): SupplyDTO
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     * @return SupplyDTO
     */
    public function setCategory(?Category $category): SupplyDTO
    {
        $this->category = $category;
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
     * @return SupplyDTO
     */
    public function setMinimumNumber(?int $minimumNumber): SupplyDTO
    {
        $this->minimumNumber = $minimumNumber;
        return $this;
    }
}
