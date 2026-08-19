<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface SurveyRepositoryInterface
{
    public function findSurveys(array $filters, int $page, int $limit): array;

    public function countSurveys(array $filters): int;
}
