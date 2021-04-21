<?php

namespace App\Entity\DTO;

use Symfony\Component\Validator\Constraints as Assert;


class RegisterUser
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
    #[Assert\Email]
    private $email;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @var bool
     */
    #[Assert\IsTrue(message: 'You need to agree to our terms.')]
    private $agreeTerms;

    /**
     * @return string
     */
    public function getForenames(): string
    {
        return $this->forenames;
    }

    /**
     * @param string $forenames
     * @return RegisterUser
     */
    public function setForenames(string $forenames): RegisterUser
    {
        $this->forenames = $forenames;
        return $this;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     * @return RegisterUser
     */
    public function setSurname(string $surname): RegisterUser
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return RegisterUser
     */
    public function setEmail(string $email): RegisterUser
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return RegisterUser
     */
    public function setPlainPassword(string $plainPassword): RegisterUser
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAgreeTerms(): bool
    {
        return $this->agreeTerms;
    }

    /**
     * @param bool $agreeTerms
     * @return RegisterUser
     */
    public function setAgreeTerms(bool $agreeTerms): RegisterUser
    {
        $this->agreeTerms = $agreeTerms;
        return $this;
    }
}