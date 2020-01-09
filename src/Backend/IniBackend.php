<?php

namespace Connector\Backend;

class IniBackend implements BackendInterface
{
    protected $path;
    protected $extension;
    
    public function __construct($path, $extension = '.ini')
    {
        $this->path = $path;
        $this->extension = $extension;
        if (!file_exists($path)) {
            throw new \RuntimeException("No such path: " . $path);
        }
    }
    
    public function getKeys($path)
    {
        $filename = $this->path . '/' . $path . $this->extension;
        
        if (!file_exists($filename)) {
            return null;
        }
        $ini = file_get_contents($filename);
        $ini = str_replace('#', ';', $ini);
        $keys = parse_ini_string($ini);
        return $keys;
    }
}
