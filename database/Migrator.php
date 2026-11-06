<?php

namespace Database;

use App\Core\Database;
use App\Core\Logger;
use PDO;

class Migrator
{
    public static function run(): void
    {
        $db = Database::getConnection();
        $schemaFile = __DIR__ . '/schema.sql';
        
        if (!file_exists($schemaFile)) {
            throw new \RuntimeException("Ficheiro schema.sql não encontrado.");
        }

        $sql = file_get_contents($schemaFile);
        $db->exec($sql);
        Logger::info("Database schema applied successfully.");
    }
}
