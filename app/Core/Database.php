<?php
/** Conexão PDO (MySQL em produção; SQLite apenas para testes automatizados). */
class Database {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (self::$pdo) return self::$pdo;
        $o = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        if ((getenv('DB_DRIVER') ?: 'mysql') === 'sqlite') {
            self::$pdo = new PDO('sqlite:' . (getenv('DB_SQLITE') ?: __DIR__ . '/../../storage/test.sqlite'), null, null, $o);
            self::$pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', getenv('DB_HOST') ?: 'localhost', getenv('DB_PORT') ?: '3306', getenv('DB_NAME') ?: 'vivere_sgc');
            self::$pdo = new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', $o);
        }
        return self::$pdo;
    }
    public static function q(string $sql, array $p = []): PDOStatement { $s = self::pdo()->prepare($sql); $s->execute($p); return $s; }
    public static function all(string $sql, array $p = []): array { return self::q($sql, $p)->fetchAll(); }
    public static function one(string $sql, array $p = []): ?array { return self::q($sql, $p)->fetch() ?: null; }
    public static function val(string $sql, array $p = []) { return self::q($sql, $p)->fetchColumn(); }
    public static function insert(string $sql, array $p = []): int { self::q($sql, $p); return (int)self::pdo()->lastInsertId(); }
}
