<?php

namespace App\Entity\DTO;

use App\Entity\HouseholdUser;

class PeriodicalReportDTO
{
    /**
     * @var HouseholdUser|null
     */
    private ?HouseholdUser $member;


    public function __construct()
    {
        $this->member = null;
    }


    /**
     * @return HouseholdUser|null
     */
    public function getMember(): ?HouseholdUser
    {
        return $this->member;
    }

    /**
     * @param HouseholdUser|null $member
     * @return PeriodicalReportDTO
     */
    public function setMember(?HouseholdUser $member): PeriodicalReportDTO
    {
        $this->member = $member;
        return $this;
    }
}
