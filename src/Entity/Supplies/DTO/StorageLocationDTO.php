<?php

namespace App\Entity\Supplies\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class StorageLocationDTO
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
     * @return StorageLocationDTO
     */
    public function setName(string $name): StorageLocationDTO
    {
        $this->name = $name;
        return $this;
    }
}
