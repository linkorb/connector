<?php

require_once(__DIR__ . '/../vendor/autoload.php');

$connector = new \Connector\Connector();

$config = $connector->getConfig('sqlite://file/tmp/test.sqlite3');
if (!$connector->exists($config)) {
    echo "Creating database\n";
    $connector->create($config);
}

$pdo = $connector->getPdo($config);
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS message (
    id INTEGER PRIMARY KEY,
    title TEXT,
    message TEXT,
    time INTEGER)"
);

$stmt = $pdo->prepare(
    "INSERT INTO message (title, message, time)
    VALUES (:title, :message, :time)"
);
$stmt->execute(['title' => 'test', 'message' => 'wola', 'time' => time()]);

$stmt = $pdo->prepare("SELECT * FROM message ORDER BY time DESC");
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
