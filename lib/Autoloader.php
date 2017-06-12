<?php

namespace Bck;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }
    
    
    /**
     * PSR-4 autoloader.
     * 
     * @access public
     * @static
     * @param mixed $className
     * @return void
     */
    public static function autoload($className)
    {
        // project-specific namespace prefix
        $prefix = 'Bck\\';

        // base directory for the namespace prefix
        $base_dir = __DIR__ . '/';

        // does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $className, $len) !== 0) {
            // no, move to the next registered autoloader
            return;
        }

        // get the relative class name
        $relative_class = substr($className, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
        // if the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    }
}