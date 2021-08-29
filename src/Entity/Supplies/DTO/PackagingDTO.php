<?php

namespace App\Entity\Supplies\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PackagingDTO
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
     * @return PackagingDTO
     */
    public function setName(string $name): PackagingDTO
    {
        $this->name = $name;
        return $this;
    }
}
