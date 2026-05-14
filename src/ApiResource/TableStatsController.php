<?php

namespace App\ApiResource;

use App\Repository\TablesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TableStatsController extends AbstractController
{
    public function __invoke(Request $request, TablesRepository $repository): JsonResponse
    {
        $numTable = $request->query->get('numTable');

        $table = $repository->findOneBy([
            'numTable' => $numTable  
        ]);

        if (!$table) {
            return $this->json([
                'error' => 'Table not found'
            ], 404);
        }

        return $this->json([
            'id' => $table->getId(),
            'table_number' => $table->getNumTable(),
            'description' => $table->getDescription(),
            'max_guests' => $table->getMaxGuests(),
            'total_guests' => $table->getGuests(),
            'present_guests' => $table->getPresentGuests(),
        ]);
    }
}