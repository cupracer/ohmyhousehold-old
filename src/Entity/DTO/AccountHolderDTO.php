<?php

namespace App\Entity\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AccountHolderDTO
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    private $name;

    /**
     * @param int $id
     * @return AccountHolderDTO
     */
    public function setId(int $id): AccountHolderDTO
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AccountHolderDTO
     */
    public function setName(string $name): AccountHolderDTO
    {
        $this->name = $name;
        return $this;
    }
}
