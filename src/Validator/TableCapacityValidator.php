<?php

namespace App\Validator;

use App\Entity\ListGuest;
use App\Repository\ListGuestRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TableCapacityValidator extends ConstraintValidator
{
    public function __construct(private ListGuestRepository $guestRepository) 
    { }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TableCapacity)
        {
            throw new UnexpectedTypeException($constraint, TableCapacity::class);
        }

        if (!$value instanceof ListGuest) { return; }

        $table = $value->getTable();

        if (!$table) { return; }

        $maxGuests = $table->getMaxGuests();

        if ($maxGuests === null) { return; }

        $count = $this->guestRepository->count(['table' => $table]);

        if ($value->getId() === null) 
        {
            if ($count >= $maxGuests) 
            {
                $this->context
                    ->buildViolation($constraint->message)
                    ->atPath('table')
                    ->addViolation();
            }

            return;
        }

        $currentTable = $this->guestRepository->countGuestsAtTableExcludingGuest($table, $value->getId());

        if ($count > $maxGuests) 
        {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('table')
                ->addViolation();
        }
    }
}