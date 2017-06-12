<?php

namespace Bck;

interface FilterInterface
{
    
    /**
     * Check path for filer. Return true if needed or false for skip.
     * 
     * @access public
     * @return bool
     */
    public function isUseable(\SplFileInfo $iterator);
}