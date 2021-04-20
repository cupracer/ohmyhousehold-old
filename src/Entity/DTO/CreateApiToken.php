<?php

namespace App\Entity\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateApiToken
{

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:1, max:255)]
    private $description;

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): CreateApiToken
    {
        $this->description = $description;
        return $this;
    }
}
