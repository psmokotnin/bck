<?php

namespace Bck;

class Ftp
{
    protected
        $conection,
        $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    public function connect()
    {
        if (!$this->config['server'])
            throw new Exception('server address not set');

        $this->conection = ftp_connect(
            $this->config['server'],
            $this->config['port']
            );
        
        if (!$this->conection)
            throw new Exception('couldn\'t connect to server');
        
        if (!ftp_login($this->conection, $this->config['login'], $this->config['password']))
            throw new Exception('wrong login or password');
        
        if ($this->config['passive'])
            ftp_pasv($this->conection, true);
    }
    
    
    public function send($src, $dst)
    {
        $dst = $this->config['path'] . $dst;
        $this->checkDir($dst);
        ftp_chdir($this->conection, '/');
        return ftp_put($this->conection, $dst, $src, FTP_BINARY);
    }
    
    public function checkDir($path)
    {
        
        $pathParts = explode('/', $path);
        array_pop($pathParts);
        
        ftp_chdir($this->conection, '/');
        foreach ($pathParts as $part) {
            
            $part = (string)$part;

            //note: $part can be '0'
            if (!@ftp_chdir($this->conection, (strlen($part) != 0 ? $part : '/'))) {
                
                ftp_mkdir($this->conection, $part);
                
                if (!ftp_chdir($this->conection, $part)) {
                    throw new Exception('can\'t create dir ' . $part);
                }
            }
        }
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    public function close()
    {
        if ($this->conection) {
            if (!ftp_close($this->conection))
                throw new Exception('unable to close ftp connection');
                
            $this->conection = NULL;
        }
    }
    
    
}