<?php

namespace Bck;

class Config
{
    protected
        $data   = array();
    
    public function __construct($data = array())
    {
        if (is_array($data))
            $this->data = $data;
    }
    
    
    /**
     * Load configuration data from ini file and create instance.
     * 
     * @access public
     * @static
     * @param string $file
     * @return Config
     */
    public static function loadFromIniFile($file)
    {
        if (is_file($file) AND is_readable($file))
            return new self(parse_ini_file($file, true));
        
        return false;
    }

    /**
     * Operator merge for configs.
     * 
     * @access public
     * @static
     * @param Config|array $config1
     * @param Config|array $config2
     * @return Config
     */
    public function merge($config1, $config2)
    {
        if ($config1 instanceof self)
            $config1 = $config1->data;
            
        if ($config2 instanceof self)
            $config2 = $config2->data;

        $config = $config1;
        if ($config2)
            foreach ($config2 as $key => $value) {
                
                //sub config array
                if ($config[$key] AND is_array($value)) {
                    $config[$key] = self::merge($config[$key], $value)->data;
                } else {
                    $config[$key] = $value;
                }
            }

        return new self($config);
    }
    
    
    /**
     * Return config value for givven key.
     * get('ftp.server.port') will return $this->data['ftp']['server.port']
     * 
     * @throw Exception when key does not exist
     * @access public
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        $data = $this->data;
        foreach (explode('.', $key, 2) as $subKey) {
            if ($data[$subKey]) {
                $data = $data[$subKey];
            } else {
                throw new Exception("configuration property $key not found");
            }
        }
        return $data;
    }
    
    public function isseted($key)
    {
        $data = $this->data;
        foreach (explode('.', $key, 2) as $subKey) {
            if ($data[$subKey]) {
                $data = $data[$subKey];
            } else {
                return false;
            }
        }
        return true;
    }
}