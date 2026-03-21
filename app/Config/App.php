<?php

/**
 * @file App.php
 * @description Configuration principale de l'application CodeIgniter 4
 */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    // URL de base — sera surchargée par la variable d'environnement app.baseURL
    public string $baseURL = 'http://localhost:8080/';

    // Index file (vide car on utilise Apache avec mod_rewrite)
    public string $indexPage = '';

    // Charset par défaut
    public string $charset = 'UTF-8';

    // Langue par défaut
    public string $defaultLocale = 'fr';

    // Fuseau horaire
    public string $appTimezone = 'Africa/Douala';

    // Clé de chiffrement (à définir dans .env)
    public string $encryptionKey = '';

    // Session
    public string $sessionDriver            = 'CodeIgniter\Session\Handlers\FileHandler';
    public string $sessionCookieName        = 'ci_session';
    public int    $sessionExpiration        = 7200;
    public string $sessionSavePath         = WRITEPATH . 'session';
    public bool   $sessionMatchIP           = false;
    public int    $sessionTimeToUpdate      = 300;
    public bool   $sessionRegenerateDestroy = false;

    // Cookie
    public string $cookiePrefix   = '';
    public string $cookieDomain   = '';
    public string $cookiePath     = '/';
    public bool   $cookieSecure   = false;
    public bool   $cookieHTTPOnly = false;
    public bool   $cookieSameSite = false;
}
