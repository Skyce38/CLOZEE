<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $c = config();
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['db_host'], $c['db_port'], $c['db_name']),
        $c['db_user'], $c['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_STRINGIFY_FETCHES => false]
    );
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}

function query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}

function transaction(callable $work): mixed
{
    for ($attempt = 0; $attempt < 3; $attempt++) {
        db()->beginTransaction();
        try { $result = $work(); db()->commit(); return $result; }
        catch (Throwable $e) {
            if (db()->inTransaction()) db()->rollBack();
            if ($e instanceof PDOException && ($e->getCode() === '40001' || ($e->errorInfo[1] ?? 0) === 1213) && $attempt < 2) {
                usleep(20000 * ($attempt + 1)); continue;
            }
            throw $e;
        }
    }
    throw new RuntimeException('Transaction could not finish');
}
