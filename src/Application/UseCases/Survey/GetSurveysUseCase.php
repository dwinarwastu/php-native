<?php

declare(strict_types=1);

namespace App\Application\UseCases\Survey;

use App\Domain\Repositories\SurveyRepositoryInterface;

class GetSurveysUseCase
{
    private SurveyRepositoryInterface $surveyRepository;

    public function __construct(SurveyRepositoryInterface $surveyRepository)
    {
        $this->surveyRepository = $surveyRepository;
    }

    public function execute(array $filters, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        $totalItems = $this->surveyRepository->countSurveys($filters);
        $surveys = $this->surveyRepository->findSurveys($filters, $page, $limit);

        $items = array_map(fn($survey) => $survey->toArray(), $surveys);
        $totalPages = (int)ceil($totalItems / $limit);

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ],
        ];
    }
}
