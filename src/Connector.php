<?php

namespace Connector;

use Connector\Backend\BackendInterface;
use PDO;
use RuntimeException;

class Connector
{
    protected $backends = [];
    public function registerBackend(BackendInterface $backend)
    {
        $this->backends[] = $backend;
    }
    
    public function getConfig($dsn)
    {
        if (filter_var($dsn, FILTER_VALIDATE_URL)) {
            $config = new Config();
            $config->setDriver(parse_url($dsn, PHP_URL_SCHEME));
            $config->setUsername(parse_url($dsn, PHP_URL_USER));
            $config->setPassword(parse_url($dsn, PHP_URL_PASS));
            $config->setAddress(parse_url($dsn, PHP_URL_HOST));
            $port = parse_url($dsn, PHP_URL_PORT);
            if ($port) {
                $config->setPort($port);
            }
            $config->setName(substr(parse_url($dsn, PHP_URL_PATH), 1));
            // TODO: support further resolving cluster/server settings
            // from backends based on address/host/server?
            return $config;
        }
        
        foreach ($this->backends as $backend) {
            $dsnKeys = $backend->getKeys($dsn);
            if ($dsnKeys) {
                $config = new Config();
                $this->loadKeys($config, $dsnKeys);
                
                if ($config->getCluster()) {
                    $clusterKeys = $backend->getKeys('clusters/' . $config->getCluster());
                    if (!$clusterKeys) {
                        throw new RuntimeException("No configuration keys found for cluster: " . $config->getCluster());
                    }
                    $this->loadKeys($config, $clusterKeys);
                }
                
                if ($config->getServer()) {
                    $serverKeys = $backend->getKeys('servers/' . $config->getServer());
                    if (!$serverKeys) {
                        throw new RuntimeException("No configuration keys found for server: " . $config->getServer());
                    }
                    $this->loadKeys($config, $serverKeys);
                }
                return $config;
            }
        }
        throw new RuntimeException("Can't resolve DSN `" . $dsn . '`');
    }
    
    public function getPdoDsn(Config $config, $mode = 'db')
    {
        switch ($mode) {
            case 'db':
            case 'server':
                break;
            default:
                throw new RuntimeException("Invalid mode: " . $mode);
        }
        $pdoDsn = $config->getDriver() . ':';
        switch ($config->getDriver()) {
            case 'pgsql':
            case 'mysql':
                $pdoDsn .= 'host=' . $config->getAddress() . ';';
                if ($config->getPort()) {
                    $pdoDsn .= 'port=' . $config->getPort() . ';';
                }
                if ($mode=='db') {
                    $pdoDsn .= 'dbname=' . $config->getName() . ';';
                }
                break;
            case 'sqlite':
                $pdoDsn .= $config->getName() . ';';
                break;
            default:
                throw new RuntimeException("Unsupported driver: " . $config->getDriver());
        }
        return $pdoDsn;
    }
    
    public function create(Config $config)
    {
        if ($this->exists($config)) {
            throw new RuntimeException("Database already exists");
        }
        // TODO: validate dbname?
        switch ($config->getDriver()) {
            case 'mysql':
                $pdo = $this->getPdo($config, 'server');
                $stmt = $pdo->prepare(
                    "CREATE DATABASE " . $config->getName()
                );
                $stmt->execute();
                break;
            default:
                throw new RuntimeException("Unsupported driver: " . $config->getDriver());
        }
    }
    
    public function drop(Config $config)
    {
        if (!$this->exists($config)) {
            throw new RuntimeException("Database does not exists");
        }
        // TODO: validate dbname?
        switch ($config->getDriver()) {
            case 'mysql':
                $pdo = $this->getPdo($config, 'server');
                $stmt = $pdo->prepare(
                    "DROP DATABASE " . $config->getName()
                );
                $stmt->execute();
                break;
            default:
                throw new RuntimeException("Unsupported driver: " . $config->getDriver());
        }
    }
    
    public function exists(Config $config)
    {
        switch ($config->getDriver()) {
            case 'mysql':
                $pdo = $this->getPdo($config, 'server');
                $stmt = $pdo->prepare(
                    "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.SCHEMATA
                    WHERE SCHEMA_NAME = :dbname"
                );
                $stmt->execute(
                    [
                        ':dbname' => $config->getName()
                    ]
                );
                return (bool) $stmt->fetchColumn();
            default:
                throw new RuntimeException("Unsupported driver");
        }
    }
    
    public function getPdo(Config $config, $mode = 'db')
    {
        $pdoDsn = $this->getPdoDsn($config, $mode);
        $config->validate(); // throws if invalid
        $pdo = new PDO($pdoDsn, $config->getUsername(), $config->getPassword());
        return $pdo;
    }
    
    public function loadKeys(Config $config, $keys)
    {
        if (!$keys) {
            return;
        }
        foreach ($keys as $key => $value) {
            $methodName = 'set' . ucfirst($key);
            if (method_exists($config, $methodName)) {
                $config->$methodName($value);
            } else {
                $config->setProperty($key, $value);
            }
        }
        return $config;
    }
}
