<?php

/**
 * @file BaseController.php
 * @description Contrôleur de base dont héritent tous les autres contrôleurs
 * Initialise les helpers et services communs
 */

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    // Helpers chargés automatiquement dans tous les contrôleurs
    protected $helpers = ['url', 'form'];

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
    }
}
