<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;


class RegisterUserRequest
{
    /**
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:1, max:255)]
    private $forenames;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    #[Assert\Length(min:1, max:255)]
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
     * @return string
     */
    public function getForenames(): string
    {
        return $this->forenames;
    }

    /**
     * @param string $forenames
     * @return RegisterUserRequest
     */
    public function setForenames(string $forenames): RegisterUserRequest
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
     * @return RegisterUserRequest
     */
    public function setSurname(string $surname): RegisterUserRequest
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
     * @return RegisterUserRequest
     */
    public function setEmail(string $email): RegisterUserRequest
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
     * @return RegisterUserRequest
     */
    public function setPlainPassword(string $plainPassword): RegisterUserRequest
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
}