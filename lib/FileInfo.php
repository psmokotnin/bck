<?php

namespace Bck;


class FileInfo
{
    protected
        $path, $basePath,
        $data = array();

    public function __construct($path, $basePath)
    {
        $this->basePath = $basePath;
        $this->path = substr($path, strlen($this->basePath));;
        $this->load();
    }
    
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
    
    public function load()
    {
        if (is_file($this->getStoragePath()))
            $this->data = json_decode(file_get_contents($this->getStoragePath()), true);
    }
    
    public function save()
    {
        $this->mtime = filemtime($this->getFullPath());

        if (!is_dir($this->getStorageDir()))
            mkdir($this->getStorageDir(), 0766, true);

        file_put_contents($this->getStoragePath(), json_encode($this->data));
    }
    
    public function __toString()
    {
        return $this->getPath();
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getFullPath()
    {
        return $this->basePath . $this->path;
    }
    
    public function getName()
    {
        return end(explode('/', $this->path));
    }
    
    protected function getStoragePath()
    {
        $sha  = sha1($this->path);
        $dir  = substr($sha, 0, 2);
        $file = substr($sha, 2);
        
        return $this->basePath . '/' . Bck::FOLDER . '/' . Bck::STORAGE . '/' . $dir . '/' . $file;
    }
    
    protected function getStorageDir()
    {
        $sha  = sha1($this->path);
        $dir  = substr($sha, 0, 2);
        $file = substr($sha, 2);
        
        return $this->basePath . '/' . Bck::FOLDER . '/' . Bck::STORAGE . '/' . $dir;
    }
    
    public function isNew()
    {
        return empty($this->data);
    }
    
    public function isModified()
    {
        return (filemtime($this->getFullPath()) != $this->data['mtime']);
    }
    
    public function isFile()
    {
        return is_file($this->getFullPath());
    }
    
    public function isDir()
    {
        return is_dir($this->getFullPath());
    }
}