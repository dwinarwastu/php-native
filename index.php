<?php

header('Content-Type: application/json; charset=utf-8');

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

$appConfig = require __DIR__ . '/config/app.php';
$dbConfig = require __DIR__ . '/config/database.php';

$origin = isset($appConfig['cors']['allowed_origins']) ? $appConfig['cors']['allowed_origins'] : '*';
$methods = isset($appConfig['cors']['allowed_methods']) ? $appConfig['cors']['allowed_methods'] : 'GET, POST, PUT, DELETE, OPTIONS';
$headers = isset($appConfig['cors']['allowed_headers']) ? $appConfig['cors']['allowed_headers'] : 'Content-Type, X-API-Key, Authorization';

header("Access-Control-Allow-Origin: {$origin}");
header("Access-Control-Allow-Methods: {$methods}");
header("Access-Control-Allow-Headers: {$headers}");

$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($requestMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $apiKeyHeaderName = isset($appConfig['auth']['header']) ? $appConfig['auth']['header'] : 'HTTP_X_API_KEY';
    $providedKey = isset($_SERVER[$apiKeyHeaderName]) ? $_SERVER[$apiKeyHeaderName] : null;

    if (!$providedKey) {
        $allHeaders = getallheaders();
        foreach ($allHeaders as $headerKey => $value) {
            if (strcasecmp($headerKey, 'X-API-KEY') === 0) {
                $providedKey = $value;
                break;
            }
        }
    }

    $masterKey = isset($appConfig['auth']['api_key']) ? $appConfig['auth']['api_key'] : '';
    if (empty($providedKey) || empty($masterKey) || !hash_equals($masterKey, (string)$providedKey)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized: Invalid or missing API Key'
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $driver = strtolower(isset($dbConfig['driver']) ? (string)$dbConfig['driver'] : 'pgsql');
    $host = isset($dbConfig['host']) ? (string)$dbConfig['host'] : 'localhost';
    $port = (int)(isset($dbConfig['port']) ? $dbConfig['port'] : ($driver === 'pgsql' ? 5432 : 3306));
    $dbName = isset($dbConfig['database']) ? (string)$dbConfig['database'] : 'php_native';
    $charset = isset($dbConfig['charset']) ? (string)$dbConfig['charset'] : 'utf8';

    if ($driver === 'pgsql') {
        $dsn = sprintf("pgsql:host=%s;port=%d;dbname=%s", $host, $port, $dbName);
    } else {
        $dsn = sprintf("mysql:host=%s;port=%d;dbname=%s;charset=%s", $host, $port, $dbName, $charset);
    }

    $pdo = new PDO(
        $dsn,
        isset($dbConfig['username']) ? (string)$dbConfig['username'] : '',
        isset($dbConfig['password']) ? (string)$dbConfig['password'] : '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    $page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
    $limit = max(1, min(100, (int)(isset($_GET['limit']) ? $_GET['limit'] : 10)));
    $offset = ($page - 1) * $limit;

    $hasFilter = (isset($_GET['date']) && $_GET['date'] !== '') ||
                 (isset($_GET['month']) && $_GET['month'] !== '') ||
                 (isset($_GET['year']) && $_GET['year'] !== '');

    $dateParam = $hasFilter ? (isset($_GET['date']) ? $_GET['date'] : '') : date('d');
    $monthParam = $hasFilter ? (isset($_GET['month']) ? $_GET['month'] : '') : date('m');
    $yearParam = $hasFilter ? (isset($_GET['year']) ? $_GET['year'] : '') : date('Y');

    $conditions = [];
    $params = [];

    if ($dateParam !== '') {
        $day = (int)$dateParam;
        if ($day >= 1 && $day <= 31) {
            if ($driver === 'pgsql') {
                $conditions[] = 'EXTRACT(DAY FROM CAST(datetime AS TIMESTAMP)) = :day';
            } else {
                $conditions[] = 'DAY(datetime) = :day';
            }
            $params[':day'] = $day;
        }
    }

    if ($monthParam !== '') {
        $month = (int)$monthParam;
        if ($month >= 1 && $month <= 12) {
            if ($driver === 'pgsql') {
                $conditions[] = 'EXTRACT(MONTH FROM CAST(datetime AS TIMESTAMP)) = :month';
            } else {
                $conditions[] = 'MONTH(datetime) = :month';
            }
            $params[':month'] = $month;
        }
    }

    if ($yearParam !== '') {
        $year = (int)$yearParam;
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
            'visit' => isset($row['visit']) ? $row['visit'] : null,
            'site' => isset($row['site']) ? $row['site'] : null,
            'resource' => isset($row['resource']) ? $row['resource'] : null,
            'nation' => isset($row['nation']) ? $row['nation'] : null,
            'bali' => isset($row['bali']) ? $row['bali'] : null,
            'visit_time' => isset($row['visit_time']) ? $row['visit_time'] : null,
            'news' => isset($row['news']) ? $row['news'] : null,
            'datetime' => isset($row['datetime']) ? $row['datetime'] : null,
        ];
    }

    $totalPages = (int)ceil($totalItems / $limit);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Surveys retrieved successfully',
        'data' => [
            'items' => $items,
            'page' => $page,
            'limit' => $limit,
            'total_items' => $totalItems,
            'total_pages' => $totalPages,
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
