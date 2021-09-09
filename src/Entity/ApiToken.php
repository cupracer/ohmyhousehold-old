<?php

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ApiTokenRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"token"}, message="This token is already in use.")
 * @UniqueEntity(
 *     fields={"user", "description"},
 *     errorPath="description",
 *     message="This description is already used for another token."
 * )
 */
class ApiToken implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="apiTokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastUsedAt;

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'createdAt' => $this->createdAt,
            'expiresAt' => $this->expiresAt,
            'lastUsedAt' => $this->lastUsedAt,
        ];
    }

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $hashAlgorithm;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLastUsedAt(): ?DateTimeInterface
    {
        return $this->lastUsedAt;
    }

    public function setLastUsedAt(?DateTimeInterface $lastUsedAt): self
    {
        $this->lastUsedAt = $lastUsedAt;

        return $this;
    }

    public function getHashAlgorithm(): ?string
    {
        return $this->hashAlgorithm;
    }

    public function setHashAlgorithm(string $hashAlgorithm): self
    {
        $this->hashAlgorithm = $hashAlgorithm;

        return $this;
    }
}
