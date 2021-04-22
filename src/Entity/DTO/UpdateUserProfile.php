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

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:2, max:10)]
    #[Assert\Locale]
    private $locale;

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

    /**
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return UpdateUserProfile
     */
    public function setLocale(string $locale): UpdateUserProfile
    {
        $this->locale = $locale;
        return $this;
    }
}
