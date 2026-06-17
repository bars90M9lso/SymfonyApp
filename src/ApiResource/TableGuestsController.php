<?php

namespace App\ApiResource;

use App\Entity\Table;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TableGuestsController extends AbstractController
{
    public function __invoke(Table $table): JsonResponse
    {
        $guests = $table->getListGuests()->toArray();
        
        if (!$guests)
        { 
            return $this->json(['error' => 'Guest not found'], 404);
        }
        
        return $this->json($guests);
    }
}
