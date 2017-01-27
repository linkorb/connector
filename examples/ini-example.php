<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Connector\Connector;
use Connector\Backend\IniBackend;

$connector = new Connector();
$backend = new IniBackend(__DIR__, '.conf');
$connector->registerBackend($backend);

$config = $connector->getConfig('example');

print_r($config);
