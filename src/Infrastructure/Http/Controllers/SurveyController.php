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

        $filters = [];

        $date = $request->get('date');
        if (!empty($date)) {
            $filters['date'] = (string)$date;
        }

        $month = $request->get('month');
        if ($month !== null && $month !== '') {
            $filters['month'] = (int)$month;
        }

        $year = $request->get('year');
        if ($year !== null && $year !== '') {
            $filters['year'] = (int)$year;
        }

        $result = $this->getSurveysUseCase->execute($filters, $page, $limit);

        Response::success($result, 'Surveys retrieved successfully');
    }
}
