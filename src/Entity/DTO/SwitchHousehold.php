<?php

namespace App\Entity\DTO;

use App\Entity\Household;
use Symfony\Component\Validator\Constraints as Assert;

class SwitchHousehold
{
    /** @var Household */
    #[Assert\NotBlank]
    private Household $household;

    public function getHousehold(): ?Household
    {
        return $this->household;
    }

    public function setHousehold(Household $household): SwitchHousehold
    {
        $this->household = $household;
        return $this;
    }
}