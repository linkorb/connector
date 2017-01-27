<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Connector\Connector;
use Connector\Backend\IniBackend;

$connector = new Connector();
$backend = new IniBackend(__DIR__, '.conf');
$connector->registerBackend($backend);

$config = $connector->getConfig('example');
if (!$connector->exists($config)) {
    echo "Creating database\n";
    $connector->create($config);
}

$pdo = $connector->getPdo($config);
