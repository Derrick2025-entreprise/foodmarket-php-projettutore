<?php

/**
 * @file Logger.php
 * @description Configuration du système de logs CodeIgniter 4
 *
 * Les logs sont écrits en format JSON dans writable/logs/
 * pour être collectés par Filebeat et envoyés vers l'ELK Stack.
 *
 * Format JSON exemple :
 * {
 *   "level": "ERROR",
 *   "message": "Database connection failed",
 *   "context": {},
 *   "timestamp": "2026-03-21T10:00:00+01:00"
 * }
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Logger extends BaseConfig
{
    /**
     * Seuil de log minimum.
     * Niveaux disponibles (du plus critique au moins critique) :
     *   emergency, alert, critical, error, warning, notice, info, debug
     */
    public string $threshold = '4';  // 4 = WARNING et au-dessus en production

    /**
     * Gestionnaire de logs — écrit dans des fichiers
     * Les fichiers sont créés dans writable/logs/
     */
    public array $handlers = [
        'CodeIgniter\Log\Handlers\FileHandler' => [
            // Niveaux de log à enregistrer
            'handles' => [
                'critical',
                'alert',
                'emergency',
                'debug',
                'error',
                'info',
                'notice',
                'warning',
            ],
            // Chemin des fichiers de log
            'path'        => WRITEPATH . 'logs/',
            // Format du nom de fichier : log-2026-03-21.log
            'fileExtension' => 'log',
            // Permissions des fichiers de log
            'filePermissions' => 0644,
            // Format JSON pour compatibilité ELK Stack
            'dateFormat' => 'Y-m-d\TH:i:sP',
        ],
    ];
}
