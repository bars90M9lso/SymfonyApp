<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class TableCapacity extends Constraint
{
    public string $message = 'table.max_guests_reached';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}