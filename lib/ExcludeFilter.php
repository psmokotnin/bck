<?php

namespace Bck;

/**
 * ExcludeFilter class.
 *
 * Patterns examples:
 * /logs - ignore folder 'logs' in project's root path
 * logs/ - ignore floder with name 'logs'
 * log - ignore all files and folders with name 'log'
 * 
 * @implements FilterInterface
 */
class ExcludeFilter extends AbstractFilter implements FilterInterface
{
    public function isUseable(\SplFileInfo $iterator)
    {
        $localPath = substr($iterator->getPathname(), strlen($this->basePath));
//        var_dump('Filter: ' . $this->pattern . ' for path : ' . $localPath);
        //Pattern from root path
        if (strpos($this->pattern, '/') === 0) {

            //pattern: /pathtofile path from the root's folder of the project
            if ($this->pattern == $localPath)
                return false;
            
            //pattern: /path/ file /path ok, but folder /path skip
            if (substr($this->pattern, strlen($this->pattern) -1) == '/') {
                if (strpos($localPath, $this->pattern) === 0)
                    return false;
            }
        }
        //patterns in subfolders
        else {
            
            // by full file name
            if ($this->pattern == $iterator->getFileName())
                return false;
            
            //skipped name in the middle in the path
            // foo/bar/baz with pattern bar shoil be skipped
            foreach (explode('/', $localPath) as $subname)
                if ($subname == $this->pattern)
                    return false;
            
        }
        return true;
    }
}