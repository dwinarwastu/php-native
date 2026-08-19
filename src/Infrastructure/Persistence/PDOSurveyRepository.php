<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Survey;
use App\Domain\Repositories\SurveyRepositoryInterface;
use PDO;

class PDOSurveyRepository implements SurveyRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findSurveys(array $filters, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        [$where, $params] = $this->applyFilters($filters);

        $sql = "SELECT * FROM survey {$where} ORDER BY datetime DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $surveys = [];

        foreach ($rows as $data) {
            $surveys[] = new Survey(
                (string)$data['username'],
                $data['visit'] ?? null,
                $data['site'] ?? null,
                $data['resource'] ?? null,
                $data['nation'] ?? null,
                $data['bali'] ?? null,
                $data['visit_time'] ?? null,
                $data['news'] ?? null,
                $data['datetime'] ?? null,
                (int)$data['id']
            );
        }

        return $surveys;
    }

    public function countSurveys(array $filters): int
    {
        [$where, $params] = $this->applyFilters($filters);

        $sql = "SELECT COUNT(*) FROM survey {$where}";
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    private function applyFilters(array $filters): array
    {
        $conditions = [];
        $params = [];
        $driver = strtolower((string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME));

        if (isset($filters['date']) && $filters['date'] !== '' && $filters['date'] !== null) {
            $day = (int)$filters['date'];
            if ($day >= 1 && $day <= 31) {
                if ($driver === 'pgsql') {
                    $conditions[] = 'EXTRACT(DAY FROM CAST(datetime AS TIMESTAMP)) = :day';
                } else {
                    $conditions[] = 'DAY(datetime) = :day';
                }
                $params[':day'] = $day;
            }
        }

        if (isset($filters['month']) && $filters['month'] !== '' && $filters['month'] !== null) {
            $month = (int)$filters['month'];
            if ($month >= 1 && $month <= 12) {
                if ($driver === 'pgsql') {
                    $conditions[] = 'EXTRACT(MONTH FROM CAST(datetime AS TIMESTAMP)) = :month';
                } else {
                    $conditions[] = 'MONTH(datetime) = :month';
                }
                $params[':month'] = $month;
            }
        }

        if (isset($filters['year']) && $filters['year'] !== '' && $filters['year'] !== null) {
            $year = (int)$filters['year'];
            if ($year > 0) {
                if ($driver === 'pgsql') {
                    $conditions[] = 'EXTRACT(YEAR FROM CAST(datetime AS TIMESTAMP)) = :year';
                } else {
                    $conditions[] = 'YEAR(datetime) = :year';
                }
                $params[':year'] = $year;
            }
        }

        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
