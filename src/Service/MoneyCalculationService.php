<?php

namespace App\Service;

class MoneyCalculationService
{
    protected const SCALE = 3;

    public function add(string $val1, string $val2): string
    {
        return bcadd($val1, $val2, $this::SCALE);
    }

    public function subtract(string $val1, string $val2): string
    {
        return bcsub($val1, $val2, $this::SCALE);
    }
}