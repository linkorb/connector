<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use Connector\Connector;
use Connector\Backend\IniBackend;

$connector = new Connector();

$config = $connector->getConfig('mysql://root:secret@localhost/example');
if (!$connector->exists($config)) {
    echo "Creating database\n";
    $connector->create($config);
}

$pdo = $connector->getPdo($config);
$stmt = $pdo->prepare("SHOW TABLES;");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
