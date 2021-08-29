<?php

namespace App\Entity\Supplies\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CategoryDTO
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
     * @return CategoryDTO
     */
    public function setName(string $name): CategoryDTO
    {
        $this->name = $name;
        return $this;
    }
}
