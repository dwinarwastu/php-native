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
        [$whereClause, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT * FROM surveys {$whereClause} ORDER BY datetime DESC LIMIT :limit OFFSET :offset";
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
        [$whereClause, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT COUNT(*) FROM surveys {$whereClause}";
        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    private function buildWhereClause(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['date'])) {
            $conditions[] = 'DATE(datetime) = :date';
            $params[':date'] = $filters['date'];
        }

        if (!empty($filters['month'])) {
            $conditions[] = 'EXTRACT(MONTH FROM datetime) = :month';
            $params[':month'] = (int)$filters['month'];
        }

        if (!empty($filters['year'])) {
            $conditions[] = 'EXTRACT(YEAR FROM datetime) = :year';
            $params[':year'] = (int)$filters['year'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$whereClause, $params];
    }
}
