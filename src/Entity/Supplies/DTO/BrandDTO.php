<?php

namespace App\Entity\Supplies\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class BrandDTO
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return BrandDTO
     */
    public function setName(string $name): BrandDTO
    {
        $this->name = $name;
        return $this;
    }
}
