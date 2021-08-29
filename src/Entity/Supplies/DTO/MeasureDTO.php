<?php

namespace App\Entity\Supplies\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MeasureDTO
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $name;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $physicalQuantity;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return MeasureDTO
     */
    public function setName(string $name): MeasureDTO
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhysicalQuantity(): string
    {
        return $this->physicalQuantity;
    }

    /**
     * @param string $physicalQuantity
     * @return MeasureDTO
     */
    public function setPhysicalQuantity(string $physicalQuantity): MeasureDTO
    {
        $this->physicalQuantity = $physicalQuantity;
        return $this;
    }
}
