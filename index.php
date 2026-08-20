<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$appConfig = require __DIR__ . '/config/app.php';
$dbConfig = require __DIR__ . '/config/database.php';

$origin = $appConfig['cors']['allowed_origins'] ?? '*';
$methods = $appConfig['cors']['allowed_methods'] ?? 'GET, POST, PUT, DELETE, OPTIONS';
$headers = $appConfig['cors']['allowed_headers'] ?? 'Content-Type, X-API-Key, Authorization';

header("Access-Control-Allow-Origin: {$origin}");
header("Access-Control-Allow-Methods: {$methods}");
header("Access-Control-Allow-Headers: {$headers}");

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $apiKeyHeaderName = $appConfig['auth']['header'] ?? 'HTTP_X_API_KEY';
    $providedKey = $_SERVER[$apiKeyHeaderName] ?? null;

    if (!$providedKey) {
        $allHeaders = getallheaders();
        foreach ($allHeaders as $headerKey => $value) {
            if (strcasecmp($headerKey, 'X-API-KEY') === 0) {
                $providedKey = $value;
                break;
            }
        }
    }

    $masterKey = $appConfig['auth']['api_key'] ?? '';
    if (empty($providedKey) || empty($masterKey) || !hash_equals($masterKey, (string)$providedKey)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized: Invalid or missing API Key'
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $driver = strtolower((string)($dbConfig['driver'] ?? 'pgsql'));
    $host = (string)($dbConfig['host'] ?? 'localhost');
    $port = (int)($dbConfig['port'] ?? ($driver === 'pgsql' ? 5432 : 3306));
    $dbName = (string)($dbConfig['database'] ?? 'php_native');
    $charset = (string)($dbConfig['charset'] ?? 'utf8');

    if ($driver === 'pgsql') {
        $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s", $host, $port, $dbName);
    } else {
        $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=%s", $host, $port, $dbName, $charset);
    }

    $pdo = new PDO(
        $dsn,
        (string)($dbConfig['username'] ?? ''),
        (string)($dbConfig['password'] ?? ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $conditions = [];
    $params = [];

    if (isset($_GET['date']) && $_GET['date'] !== '') {
        $day = (int)$_GET['date'];
        if ($day >= 1 && $day <= 31) {
            if ($driver === 'pgsql') {
                $conditions[] = 'EXTRACT(DAY FROM CAST(datetime AS TIMESTAMP)) = :day';
            } else {
                $conditions[] = 'DAY(datetime) = :day';
            }
            $params[':day'] = $day;
        }
    }

    if (isset($_GET['month']) && $_GET['month'] !== '') {
        $month = (int)$_GET['month'];
        if ($month >= 1 && $month <= 12) {
            if ($driver === 'pgsql') {
                $conditions[] = 'EXTRACT(MONTH FROM CAST(datetime AS TIMESTAMP)) = :month';
            } else {
                $conditions[] = 'MONTH(datetime) = :month';
            }
            $params[':month'] = $month;
        }
    }

    if (isset($_GET['year']) && $_GET['year'] !== '') {
        $year = (int)$_GET['year'];
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

    $countSql = "SELECT COUNT(*) FROM survey {$where}";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $val) {
        $countStmt->bindValue($key, $val);
    }
    $countStmt->execute();
    $totalItems = (int)$countStmt->fetchColumn();

    $dataSql = "SELECT * FROM survey {$where} ORDER BY datetime DESC LIMIT :limit OFFSET :offset";
    $dataStmt = $pdo->prepare($dataSql);

    foreach ($params as $key => $val) {
        $dataStmt->bindValue($key, $val);
    }

    $dataStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();

    $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    $items = [];

    foreach ($rows as $row) {
        $items[] = [
            'id' => (int)$row['id'],
            'username' => (string)$row['username'],
            'visit' => $row['visit'] ?? null,
            'site' => $row['site'] ?? null,
            'resource' => $row['resource'] ?? null,
            'nation' => $row['nation'] ?? null,
            'bali' => $row['bali'] ?? null,
            'visit_time' => $row['visit_time'] ?? null,
            'news' => $row['news'] ?? null,
            'datetime' => $row['datetime'] ?? null,
        ];
    }

    $totalPages = (int)ceil($totalItems / $limit);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Surveys retrieved successfully',
        'data' => [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_items' => $totalItems,
                'total_pages' => $totalPages,
            ]
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
