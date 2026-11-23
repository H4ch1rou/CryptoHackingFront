<?php
/**
 * Conexión PDO a SQLite + creación del esquema y datos semilla.
 *
 * init_db() es idempotente: puede ejecutarse en cada arranque del contenedor
 * (seed/init.php) y también se invoca de forma perezosa si la base no existe.
 */

declare(strict_types=1);

const SCHEMA_VERSION = 1;

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    // WAL + busy_timeout: permite peticiones concurrentes sin bloqueos.
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 8000');
    $pdo->exec('PRAGMA foreign_keys = ON');

    $version = (int) $pdo->query('PRAGMA user_version')->fetchColumn();
    if ($version < SCHEMA_VERSION) {
        init_db($pdo);
    }
    return $pdo;
}

/** Crea tablas y datos semilla. Seguro de ejecutar varias veces. */
function init_db(PDO $pdo): void
{
    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS students (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT NOT NULL,
            token       TEXT NOT NULL UNIQUE,
            created_at  TEXT NOT NULL DEFAULT (datetime('now'))
        );

        -- Progreso por alumno y laboratorio.
        CREATE TABLE IF NOT EXISTS progress (
            student_id    INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
            lab_id        TEXT NOT NULL,
            started_at    TEXT NOT NULL DEFAULT (datetime('now')),
            completed_at  TEXT,
            hints_at_completion INTEGER,
            PRIMARY KEY (student_id, lab_id)
        );

        -- Pistas desbloqueadas por alumno y laboratorio.
        CREATE TABLE IF NOT EXISTS hint_usage (
            student_id  INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
            lab_id      TEXT NOT NULL,
            hints_used  INTEGER NOT NULL DEFAULT 0,
            updated_at  TEXT NOT NULL DEFAULT (datetime('now')),
            PRIMARY KEY (student_id, lab_id)
        );

        -- Intentos de verificación de flags (para rate limit y auditoría).
        CREATE TABLE IF NOT EXISTS flag_attempts (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            student_id  INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
            lab_id      TEXT NOT NULL,
            ok          INTEGER NOT NULL,
            created_at  TEXT NOT NULL DEFAULT (datetime('now'))
        );
        CREATE INDEX IF NOT EXISTS idx_attempts ON flag_attempts(student_id, created_at);

        -- Estado clave/valor de cada laboratorio por alumno.
        CREATE TABLE IF NOT EXISTS lab_state (
            student_id  INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
            lab_id      TEXT NOT NULL,
            k           TEXT NOT NULL,
            v           TEXT,
            PRIMARY KEY (student_id, lab_id, k)
        );
    SQL);

    $pdo->exec('PRAGMA user_version = ' . SCHEMA_VERSION);
}
