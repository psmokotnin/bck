<?php
namespace Bck;

abstract class AbstractFilter 
{
    protected
        $pattern,
        $basePath;

    public function __construct($pattern, $basePath)
    {
        $this->pattern = trim($pattern);
        $this->basePath = $basePath;
    }
    
    public function __toString()
    {
        return $this->pattern;
    }
    
    public function isValid()
    {
        //empty pattern
        if (empty($this->pattern))
            return false;
        
        //comment
        if (strpos($this->pattern, '#') === 0)
            return false;
        
        //DEBUG
        //echo 'ADD FILTER: ' . $this . "\n";
        
        return true;
    }
}