<?php

namespace App\ApiResource;

use App\Repository\TableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TableStatsController extends AbstractController
{
    public function __invoke(Request $request, TableRepository $repository): JsonResponse
    {
        $numTable = $request->query->get('numTable');

        if ($numTable === null || $numTable === '') 
        {
            $tables = $repository->findAll();

            return $this->json(array_map(function ($table) 
            {
                return $this->tableToStats($table);
            }, $tables));
        }

        $table = $repository->findOneBy([
            'numTable' => $numTable  
        ]);

        if (!$table) 
        {
            return $this->json(['error' => 'Table not found'], 404);
        }

        return $this->tableToStats($table);
    }

    private function tableToStats(Table $table): array
    {
        return [
            'id' => $table->getId(),
            'number' => $table->getNumTable(),
            'max_guests' => $table->getMaxGuests(),
            'total_guests' => $table->getGuests(),
            'present_guests' => $table->getPresentGuests(),
        ];
    }
}
