<?php
// ============================================================
//  config.php — Database connection via PDO
// ============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'englight');
define('DB_USER', 'root');
define('DB_PASS', '');          // Change in production
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',    'EngLight');
define('APP_URL',     'http://localhost/englight');
define('APP_VERSION', '1.0.0');

/**
 * Returns a singleton PDO instance.
 */
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log the error rather than display it
            error_log('DB Connection failed: ' . $e->getMessage());
            die('<div style="font-family:sans-serif;padding:2rem;color:#c00">
                    <strong>Database connection error.</strong> Please contact the administrator.
                 </div>');
        }
    }
    return $pdo;
}

/**
 * Shorthand: run a prepared statement and return the PDOStatement.
 */
function db_run(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch one row.
 */
function db_row(string $sql, array $params = []): array|false {
    return db_run($sql, $params)->fetch();
}

/**
 * Fetch all rows.
 */
function db_all(string $sql, array $params = []): array {
    return db_run($sql, $params)->fetchAll();
}

/**
 * XSS-safe output helper.
 */
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
