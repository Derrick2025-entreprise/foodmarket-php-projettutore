<?php

namespace Config;

/**
 * @file Paths.php
 * @description Chemins système de CodeIgniter 4
 */
class Paths
{
    /**
     * Chemin vers le dossier système de CI4
     */
    public string $systemDirectory = __DIR__ . '/../../vendor/codeigniter4/framework/system';

    /**
     * Chemin vers le dossier app
     */
    public string $appDirectory = __DIR__ . '/..';

    /**
     * Chemin vers le dossier writable
     */
    public string $writableDirectory = __DIR__ . '/../../writable';

    /**
     * Chemin vers le dossier tests
     */
    public string $testsDirectory = __DIR__ . '/../../tests';

    /**
     * Chemin vers le dossier views
     */
    public string $viewDirectory = __DIR__ . '/../Views';
}
