<?php

namespace App\Entity\DTO;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class AssetAccountDTO
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
    private string $name;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    private string $accountType;

    #[Assert\NotBlank]
    private string $initialBalance;

    /**
     * @var DateTimeInterface
     */
    #[Assert\NotBlank]
    #[Assert\Type("\DateTimeInterface")]
    private $initialBalanceDate;

    /**
     * @var string
     */
    private $iban;

    #[Assert\NotBlank]
    /**
     * @var Collection
     */
    private $owners;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AssetAccountDTO
     */
    public function setName(string $name): AssetAccountDTO
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountType(): string
    {
        return $this->accountType;
    }

    /**
     * @param string $accountType
     * @return AssetAccountDTO
     */
    public function setAccountType(string $accountType): AssetAccountDTO
    {
        $this->accountType = $accountType;
        return $this;
    }

    /**
     * @return string
     */
    public function getInitialBalance(): string
    {
        return $this->initialBalance;
    }

    /**
     * @param string $initialBalance
     * @return AssetAccountDTO
     */
    public function setInitialBalance(string $initialBalance): AssetAccountDTO
    {
        $this->initialBalance = $initialBalance;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getInitialBalanceDate(): DateTimeInterface
    {
        return $this->initialBalanceDate;
    }

    /**
     * @param DateTimeInterface $initialBalanceDate
     * @return AssetAccountDTO
     */
    public function setInitialBalanceDate(DateTimeInterface $initialBalanceDate): AssetAccountDTO
    {
        $this->initialBalanceDate = $initialBalanceDate;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @param string|null $iban
     * @return AssetAccountDTO
     */
    public function setIban(?string $iban): AssetAccountDTO
    {
        $this->iban = $iban;
        return $this;
    }

    /**
     * @return Collection|null
     */
    public function getOwners(): ?Collection
    {
        return $this->owners;
    }

    /**
     * @param Collection $owners
     * @return AssetAccountDTO
     */
    public function setOwners(Collection $owners): AssetAccountDTO
    {
        $this->owners = $owners;
        return $this;
    }
}
