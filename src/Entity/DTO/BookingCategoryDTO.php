<?php

namespace App\Entity\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class BookingCategoryDTO
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
     * @return BookingCategoryDTO
     */
    public function setName(string $name): BookingCategoryDTO
    {
        $this->name = $name;
        return $this;
    }
}
