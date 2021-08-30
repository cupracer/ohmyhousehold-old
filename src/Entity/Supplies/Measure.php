<?php

namespace App\Entity\Supplies;

use App\Entity\Household;
use App\Repository\Supplies\MeasureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=MeasureRepository::class)
 * @ORM\Table(name="supplies_measure")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *     fields={"name", "household"},
 *     errorPath="name",
 *     message="This name is already in use in this household."
 *     )
 * @UniqueEntity(
 *     fields={"name", "physicalQuantity"},
 *     errorPath="name",
 *     message="This name already exists for the chosen physical quantity."
 *     )
 */
class Measure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Household::class, inversedBy="supplyMeasures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $household;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $physicalQuantity;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getHousehold(): ?Household
    {
        return $this->household;
    }

    public function setHousehold(?Household $household): self
    {
        $this->household = $household;

        return $this;
    }

    public function getPhysicalQuantity(): ?string
    {
        return $this->physicalQuantity;
    }

    public function setPhysicalQuantity(string $physicalQuantity): self
    {
        $this->physicalQuantity = $physicalQuantity;

        return $this;
    }
}
