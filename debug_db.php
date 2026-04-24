<?php
$_SERVER['CI_ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');
define('HOMEPATH', realpath('.') . DIRECTORY_SEPARATOR);
require 'app/Config/Paths.php';
$paths = new Config\Paths();
define('APPPATH',    realpath($paths->appDirectory) . DIRECTORY_SEPARATOR);
define('ROOTPATH',   realpath(APPPATH . '../') . DIRECTORY_SEPARATOR);
define('SYSTEMPATH', realpath($paths->systemDirectory) . DIRECTORY_SEPARATOR);
define('WRITEPATH',  realpath($paths->writableDirectory) . DIRECTORY_SEPARATOR);
require 'vendor/autoload.php';
require 'app/Config/Database.php';
$db = new Config\Database();
echo 'WRITEPATH=' . WRITEPATH . PHP_EOL;
echo 'database=' . $db->tests['database'] . PHP_EOL;
$fullPath = str_contains($db->tests['database'], DIRECTORY_SEPARATOR)
    ? $db->tests['database']
    : WRITEPATH . $db->tests['database'];
echo 'fullPath=' . $fullPath . PHP_EOL;
echo 'exists=' . (file_exists($fullPath) ? 'OUI' : 'NON') . PHP_EOL;
try {
    $sqlite = new SQLite3($fullPath);
    echo 'SQLite3 OK' . PHP_EOL;
    $sqlite->close();
} catch (Exception $e) {
    echo 'SQLite3 ERREUR: ' . $e->getMessage() . PHP_EOL;
}
