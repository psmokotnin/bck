<?php

namespace Bck;

class DirectoryIterator extends \DirectoryIterator
{
    protected
        $filters = array();
    

    public function addFilter(FilterInterface $filter)
    {
        if ($filter->isValid())
            $this->filters[] = $filter;
    }

    public function next ()
    {

        parent::next();
        if (!$this->checkFilters($this->current())) {
            //DEBUG
            //print 'SKIP: ' . $this->getPathname() . ' because of: ' . $filter . "\n";
            $this->next();
        }
    }
    
    public function checkFilters(\SplFileInfo $fileInfo)
    {
        foreach ($this->filters as $filter) {//echo 'f '.$filter.'  p '.$fileInfo."\n";
            if (!$filter->isUseable($fileInfo)) {
                //echo 'SKIP ' . $fileInfo . "\n";
                return false;
            }
        }
        return true;
    }
}