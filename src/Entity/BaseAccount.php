<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"name", "household"},
 *     errorPath="name",
 *     message="This name is already in use in this household."
 *     )
 */
abstract class BaseAccount implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $initialBalance;

    /**
     * @ORM\Column(type="date")
     */
    private $initialBalanceDate;

    /**
     * @ORM\Column(type="string", length=34, nullable=true)
     */
    private $iban;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;


    public function jsonSerialize()
    {
        return [
            "iban" => $this->iban,
            "initialBalance" => $this->initialBalance,
            "initialBalanceDate" => $this->initialBalanceDate,
            "createdAt" => $this->createdAt,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInitialBalance(): ?string
    {
        return $this->initialBalance;
    }

    public function setInitialBalance(string $initialBalance): self
    {
        $this->initialBalance = $initialBalance;

        return $this;
    }

    public function getInitialBalanceDate(): ?DateTimeInterface
    {
        return $this->initialBalanceDate;
    }

    public function setInitialBalanceDate(DateTimeInterface $initialBalanceDate): self
    {
        $this->initialBalanceDate = $initialBalanceDate;

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new DateTime();
    }
}
