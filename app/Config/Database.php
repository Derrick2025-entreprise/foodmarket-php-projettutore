<?php

/**
 * @file Database.php
 * @description Configuration de la base de données
 * Les valeurs sont surchargées par les variables d'environnement dans .env
 */

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    // Base de données par défaut (utilisée en dev et prod)
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'db',           // nom du service Docker
        'username'     => 'fooduser',
        'password'     => 'foodpass',
        'database'     => 'foodmarket',
        'DBDriver'     => 'MySQLi',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8mb4',
        'DBCollat'     => 'utf8mb4_general_ci',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 3306,
        'numberNative' => false,
    ];

    // Base de données de test (SQLite en mémoire — pas besoin de MySQL pour les tests)
    public array $tests = [
        'DSN'      => '',
        'hostname' => '127.0.0.1',
        'username' => '',
        'password' => '',
        'database' => ':memory:',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => 'db_',
        'port'     => 3306,
    ];
}
