<?php

/**
 * @file public/index.php
 * @description Point d'entrée de l'application CodeIgniter 4
 * Toutes les requêtes HTTP passent par ce fichier (via .htaccess)
 */

// Chemin vers le dossier de l'application
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Charge le bootstrap de CodeIgniter
require realpath(FCPATH . '../vendor/codeigniter4/framework/system/bootstrap.php');
