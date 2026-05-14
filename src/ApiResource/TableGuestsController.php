<?php

namespace App\ApiResource;

use App\Entity\Tables;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TableGuestsController extends AbstractController
{
    public function __invoke(Tables $table): JsonResponse
    {
        $guests = $table->getListGuests()->toArray();           
        return $this->json($guests);
    }
}
