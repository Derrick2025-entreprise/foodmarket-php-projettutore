<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    public string $defaultGroup = 'default';

    public array $default = [
        'DSN'          => '',
        'hostname'     => 'db',
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

    public array $tests = [
        'DSN'      => '',
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => 'writable/tests.db',
        'DBDriver' => 'SQLite3',
        'DBPrefix' => '',
        'port'     => 3306,
    ];

    public function __construct()
    {
        parent::__construct();

        // En mode testing : tout pointe vers SQLite, chemin absolu via WRITEPATH
        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup       = 'tests';
            $this->tests['database']  = WRITEPATH . 'tests.db';
            $this->default['DBDriver'] = 'SQLite3';
            $this->default['database'] = WRITEPATH . 'tests.db';
            $this->default['hostname'] = '';
            $this->default['username'] = '';
            $this->default['password'] = '';
            $this->default['port']     = 3306;
        }
    }
}
