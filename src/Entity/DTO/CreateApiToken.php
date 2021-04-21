<?php

namespace App\Entity\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateApiToken
{

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:255)]
    #[Assert\Regex('/^[a-z0-9][a-z0-9\.\-_\s]*[a-z0-9]$/i')]
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
