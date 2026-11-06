<?php

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Logger.php';
require_once __DIR__ . '/Migrator.php';
require_once __DIR__ . '/Seeder.php';

try {
    echo "A iniciar migração da base de dados JFS Properties...\n";
    \Database\Migrator::run();
    echo "Migração concluída com sucesso.\n";

    echo "A semear dados essenciais e operacionais...\n";
    \Database\Seeder::run();
    echo "Dados semeados com sucesso.\n";
} catch (\Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
