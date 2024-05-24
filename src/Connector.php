<?php

namespace Connector;

use Connector\Backend\BackendInterface;

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
            $config->setDriver(urldecode(parse_url($dsn, PHP_URL_SCHEME)));
            $config->setUsername(urldecode(parse_url($dsn, PHP_URL_USER)));
            $config->setPassword(urldecode(parse_url($dsn, PHP_URL_PASS)));
            $config->setAddress(urldecode(parse_url($dsn, PHP_URL_HOST)));
            $port = urldecode(parse_url($dsn, PHP_URL_PORT));
            $arguments = urldecode(parse_url($dsn, PHP_URL_QUERY));
            parse_str($arguments, $arguments); // parse ?x=y&a=b format into k/v array

            foreach ($arguments as $k => $v) {
                $config->setArgument($k, $v);
            }

            if ($port) {
                $config->setPort($port);
            }
            $urlPath = parse_url($dsn, PHP_URL_PATH);
            if ('sqlite' == parse_url($dsn, PHP_URL_SCHEME)) {
                $i = pathinfo($urlPath);
                $config->setName($i['filename']);
                $config->setFileName($urlPath);
            } else {
                $config->setName(substr($urlPath, 1));
            }

            // query pramater
            parse_str(parse_url($dsn, PHP_URL_QUERY), $params);
            if ($params) {
                $config->setQueries($params);
            }

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
                    $clusterKeys = $backend->getKeys('clusters/'.$config->getCluster());
                    if ($clusterKeys) {
                        $this->loadKeys($config, $clusterKeys);
                    }
                }

                if ($config->getServer()) {
                    $serverKeys = $backend->getKeys('servers/'.$config->getServer());
                    if ($serverKeys) {
                        $this->loadKeys($config, $serverKeys);
                    } else {
                        $config->setAddress($config->getServer());
                    }
                }

                return $config;
            }
        }
        throw new \RuntimeException("Can't resolve DSN `".$dsn.'`');
    }

    public function getPdoDsn(Config $config, $mode = 'db')
    {
        switch ($mode) {
            case 'db':
            case 'server':
                break;
            default:
                throw new \RuntimeException('Invalid mode: '.$mode);
        }
        $pdoDsn = $config->getDriver().':';
        switch ($config->getDriver()) {
            case 'pgsql':
            case 'mysql':
                $pdoDsn .= 'host='.$config->getAddress().';';
                if ($config->getPort()) {
                    $pdoDsn .= 'port='.$config->getPort().';';
                }
                if ('db' == $mode) {
                    $pdoDsn .= 'dbname='.$config->getName().';';
                }
                $pdoDsn .= 'charset='.($config->getQuery('charset') ? $config->getQuery('charset') : 'utf8;');
                break;
            case 'sqlite':
                $pdoDsn .= $config->getFileName();
                break;
            case 'sqlsrv':
                $pdoDsn .= 'Server='.$config->getAddress().';Database='.$config->getName();
                foreach ($config->getArguments() as $k => $v) {
                    $pdoDsn .= ';'.$k.'='.$v;
                }
                break;
            default:
                throw new \RuntimeException('Unsupported driver: '.$config->getDriver());
        }

        return $pdoDsn;
    }

    public function create(Config $config)
    {
        if ($this->exists($config)) {
            throw new \RuntimeException('Database already exists');
        }
        // TODO: validate dbname?
        switch ($config->getDriver()) {
            case 'mysql':
                $pdo = $this->getPdo($config, 'server');
                $stmt = $pdo->prepare(
                    'CREATE DATABASE '.$config->getName()
                );
                $stmt->execute();
                break;
            default:
                throw new \RuntimeException('Unsupported driver: '.$config->getDriver());
        }
    }

    public function drop(Config $config)
    {
        if (!$this->exists($config)) {
            throw new \RuntimeException('Database does not exists');
        }
        // TODO: validate dbname?
        switch ($config->getDriver()) {
            case 'mysql':
                $pdo = $this->getPdo($config, 'server');
                $stmt = $pdo->prepare(
                    'DROP DATABASE '.$config->getName()
                );
                $stmt->execute();
                break;
            default:
                throw new \RuntimeException('Unsupported driver: '.$config->getDriver());
        }
    }

    public function exists(Config $config)
    {
        switch ($config->getDriver()) {
            case 'mysql':
                $pdo = $this->getPdo($config, 'server');
                $stmt = $pdo->prepare(
                    'SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.SCHEMATA
                    WHERE SCHEMA_NAME = :dbname'
                );
                $stmt->execute(
                    [
                        ':dbname' => $config->getName(),
                    ]
                );

                return (bool) $stmt->fetchColumn();
            case 'sqlite':
                return $this->getPdo($config, 'server');
                break;
            default:
                throw new \RuntimeException('Unsupported driver');
        }
    }

    public function getPdo(Config $config, $mode = 'db')
    {
        $pdoDsn = $this->getPdoDsn($config, $mode);
        $config->validate(); // throws if invalid

        return new \PDO($pdoDsn, $config->getUsername(), $config->getPassword());
    }

    public function loadKeys(Config $config, $keys)
    {
        if (!$keys) {
            return;
        }
        foreach ($keys as $key => $value) {
            $methodName = 'set'.ucfirst($key);
            if (method_exists($config, $methodName)) {
                $config->$methodName($value);
            } else {
                $config->setProperty($key, $value);
            }
        }

        return $config;
    }
}
