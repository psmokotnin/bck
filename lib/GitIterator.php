<?php

namespace Bck;

class GitIterator extends DirectoryIterator
{
    protected
        $list = NULL;

    public function __construct($path)
    {
        parent::__construct($path);
    }
    
    private function checkList()
    {
        if (is_null($this->list))
        {
            $this->list = array();
            $this->ls(true);
            $this->ls();
        }
    }
    
    /**
     * Collect all files and folders which are ignored by Git.
     * 
     * @access protected
     * @return void
     */
    protected function ls($directory = false)
    {
        //exec("git status --ignored -s | grep '!!'", $returnStrings, $returnCode);
        exec("git ls-files --others -i --exclude-standard" . ($directory ? " --directory" : ""), $returnStrings, $returnCode);
        
        if ($returnCode != 0)
            throw new Exception('run git status failed');
        
        foreach ($returnStrings as $path) {
            /*
            //By git status command:
            if (strpos($path, '!! ') !== 0)
                continue;
            $path = substr($path, 3);
            */
            
            $fileInfo = new SplFileInfo($this->getPath() . '/' . $path);
            //echo 'CHECK ' . $fileInfo . "\n";
            if ($this->checkFilters($fileInfo))
                $this->list[$path] = $fileInfo;
        }
    }
    
    
    /**
     * Rewrite SeekableIterator method seek.
     * 
     * @access public
     * @param mixed $position
     * @return bool
     */
    public function seek($position)
    {
        $this->checkList();
        return isset($this->list[$position]);
    }
    
    
    public function current()
    {
        $this->checkList();
        return current($this->list);
    }
    
    public function key()
    {
        $this->checkList();
        return key($this->list);
    }
    
    public function next()
    {
        $this->checkList();
        next($this->list);
    }
    
    public function rewind()
    {
        $this->checkList();
        reset($this->list);
    }

    public function valid()
    {
        $this->checkList();
        return key($this->list) !== NULL;
    }
}