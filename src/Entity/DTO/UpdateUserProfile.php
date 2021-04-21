<?php

namespace App\Entity\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserProfile
{

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:255)]
    #[Assert\Regex('/^[a-z][a-z\-\s]*[a-z]$/i')]
    private $forenames;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:255)]
    #[Assert\Regex('/^[a-z][a-z\-\s]*[a-z]$/i')]
    private $surname;

    public function getForenames(): ?string
    {
        return $this->forenames;
    }

    public function setForenames(string $forenames): UpdateUserProfile
    {
        $this->forenames = $forenames;
        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): UpdateUserProfile
    {
        $this->surname = $surname;
        return $this;
    }
}
