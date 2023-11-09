<?php

namespace Connector;

class Config
{
    protected $name;
    protected $username;
    protected $password;
    protected $driver = 'mysql';
    protected $server;
    protected $cluster;
    protected $address;
    protected $port = 3306;
    protected $properties = []; // arbitrary database properties such as name, description, tier, etc
    protected $arguments = []; // DSN arguments
    protected $fileName;
    protected $queries = [];

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    public function getCluster()
    {
        return $this->cluster;
    }

    public function setCluster($cluster)
    {
        $this->cluster = $cluster;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function setDriver($driver)
    {
        $this->driver = $driver;
        switch ($driver) {
            case 'mysql':
                $this->port = 3306;
                break;
        }

        return $this;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;

        return $this;
    }

    public function getProperty($key)
    {
        if (!isset($this->properties[$key])) {
            return null;
        }

        return $this->properties[$key];
    }

    public function setArgument($key, $value)
    {
        $this->arguments[$key] = $value;

        return $this;
    }

    public function getArgument($key)
    {
        if (!isset($this->arguments[$key])) {
            return null;
        }

        return $this->arguments[$key];
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function validate()
    {
        if (!$this->getName()) {
            throw new \RuntimeException('Missing name');
        }
        switch ($this->getDriver()) {
            case 'mysql':
            case 'pgsql':
                if (!$this->getUsername()) {
                    throw new \RuntimeException('Missing username');
                }
                if (!$this->getUsername()) {
                    throw new \RuntimeException('Missing password');
                }
                if (!$this->getAddress()) {
                    throw new \RuntimeException('Missing address');
                }
                if (!$this->getPort()) {
                    throw new \RuntimeException('Missing port');
                }
                break;
            case 'sqlite':
                break;
            case 'sqlsrv':
                if (!$this->getAddress()) {
                    throw new \RuntimeException('Missing address');
                }
                break;
            default:
                throw new \RuntimeException('Unsupported driver: '.$this->getDriver());
        }
    }

    /**
     * @param [array] $array
     */
    public function setQueries($array = []): self
    {
        $this->queries = $array;

        return $this;
    }

    public function getQuery($key)
    {
        if (!isset($this->queries[$key])) {
            return null;
        }

        return $this->queries[$key];
    }
}
