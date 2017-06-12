<?php
namespace Bck;

class Bck
{
    const VERSION           = 0.1;
    const FOLDER            = '.bck';
    const STORAGE           = 'objects';
    const IGNOREFILE        = '.bckignore';
    const GITSOURCE         = '.gitignore';
    const CONFIGFILE        = 'config.ini';
    const CONFIGGLOBALFILE  = '/../config/global.ini';
    
    
    protected
        $path,  //working dir
        $ftp,   //ftp connection
        $config, $local, $global, //configs: result, local and global
        $filters = null // iterator's filters
        ;

    public function __construct($path)
    {
        $this->path = $path;
        $this->loadConfigs();
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getFtp()
    {
        if (!$this->ftp)
            $this->ftp = new Ftp($this->config->get('ftp'));

        return $this->ftp;
    }
    
    public function loadConfigs()
    {
        $this->local  = Config::loadFromIniFile($this->path . '/' . self::FOLDER . '/' . self::CONFIGFILE);
        $this->global = Config::loadFromIniFile(__DIR__ . self::CONFIGGLOBALFILE);
        $this->config = Config::merge($this->global, $this->local);
    }
    
    public function getConfig($global = false)
    {
        if ($global)
            return $this->global;

        return $this->config;
    }
    
    /**
     * Init project for bck.
     * 
     * @access public
     * @return void
     */
    public function init()
    {
        if (is_dir($this->path . '/' . self::FOLDER)) {
            echo "folder already initialized\n";
            return 1;
        }
        
        if (is_writable($this->path)) {
            mkdir($this->path . '/' . self::FOLDER);
            mkdir($this->path . '/' . self::FOLDER . '/' . self::STORAGE);
            touch($this->path . '/' . self::FOLDER . '/' . self::CONFIGFILE);
        }
        else {
            echo "Can't write to folder. Check permissions.\n";
        }
    }
    
    /**
     * Clean project from all bck data.
     * 
     * @access public
     * @return void
     */
    public function clean()
    {
        $rm = function($path) use (&$rm) {
            
            if (is_dir($path)) {
                $dir = opendir($path);
                while ($file = readdir($dir)) {
                    if ($file == '.' OR $file == '..')
                        continue;
                    $rm($path . '/' . $file);
                }
                return rmdir($path);
            } elseif (is_file($path)) {
                return unlink($path);
            }
        };
        return $rm($this->path . '/' . self::FOLDER);
    }
    
    
    /**
     * Scan for files for backup.
     * 
     * @access public
     * @return array of pathes
     */
    public function scan($path = NULL, $callback = null)
    {
        if ($path OR $this->config->get('scan.source') == 'all') {
            $iterator = new DirectoryIterator(($path ? $path : $this->getPath()));
        } elseif ($this->config->get('scan.source') == 'git') {
            $iterator = new GitIterator($this->getPath());
        } else
            throw new Exception('unknown source type');

        //get slow mode configuration
        $slow = 0;
        if ($this->config->isseted('scan.slow'))
            $slow = (int)$this->config->get('scan.slow');

        foreach ($this->getIteratorFilters() as $filter)
            $iterator->addFilter($filter);

        foreach ($iterator as $file) {
            if ($file->isDot() OR $file->isLink())
                continue;
            
            //slow mode
            if ($slow)
                usleep($slow);
            
            $fileInfo = new FileInfo((string)$file->getPathname(), $this->getPath());

            if ($file->isFile()) {

                if ($fileInfo->isNew() OR $fileInfo->isModified()) {
                    
                    if (is_callable($callback))
                        $callback($fileInfo);
                }
            } elseif ($file->isDir()) {
                $this->scan($file->getPathname(), $callback);
            }
            unset($fileInfo);
        }
        
        return $files;
    }
    
    public function push($callback = null, $path = null)
    {
        $ftp = $this->getFtp();
        $this->scan($path, function($file) use($callback, $ftp) {
            
            if ($file->isFile($file)) {
                if (is_callable($callback))
                    $callback($file);
                
                $result = $ftp->send($file->getFullPath(), $file->getPath());
                if ($result) {
                   $file->save();
                }
                
                $callback($file, $result);
            }
        });
    }
    
    protected function getIteratorFilters()
    {
        if (is_null($this->filters)) {
            
            $this->filters = array();
            
            //ignore .bck folder
            $this->filters[] = new ExcludeFilter('/' . self::FOLDER, $this->getPath());
            
            //bckignorefile
            if (
                $this->config->isseted('scan.filter') AND
                $this->config->get('scan.filter') AND
                is_file(self::IGNOREFILE)
            )
                foreach(file(self::IGNOREFILE) as $ignore) {
                    $this->filters[] = new ExcludeFilter($ignore, $this->getPath());
                }
        
        }
        return $this->filters;
    }
    
}