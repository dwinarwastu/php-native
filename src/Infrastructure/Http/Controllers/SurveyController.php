<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\UseCases\Survey\GetSurveysUseCase;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;

class SurveyController
{
    private GetSurveysUseCase $getSurveysUseCase;

    public function __construct(GetSurveysUseCase $getSurveysUseCase)
    {
        $this->getSurveysUseCase = $getSurveysUseCase;
    }

    public function index(Request $request): void
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);

        $date = $request->get('date');
        $month = $request->get('month');
        $year = $request->get('year');

        $hasFilter = ($date !== null && $date !== '') ||
                     ($month !== null && $month !== '') ||
                     ($year !== null && $year !== '');

        $filters = [];

        if (!$hasFilter) {
            $filters['date'] = (int)date('d');
            $filters['month'] = (int)date('m');
            $filters['year'] = (int)date('Y');
        } else {
            if ($date !== null && $date !== '') {
                $filters['date'] = (int)$date;
            }
            if ($month !== null && $month !== '') {
                $filters['month'] = (int)$month;
            }
            if ($year !== null && $year !== '') {
                $filters['year'] = (int)$year;
            }
        }

        $result = $this->getSurveysUseCase->execute($filters, $page, $limit);

        Response::success($result, 'Surveys retrieved successfully');
    }
}
