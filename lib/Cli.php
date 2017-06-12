<?php

namespace Bck;

class Cli
{
    protected
        $currentPath, //running path
        $options = array(),
        $args,        //cli args
        $bck,
        $runtime;
        
    public function __construct($path, $args)
    {
        $this->currentPath = $path;
        $this->runtime = microtime(true);
        $this->args = $args;
        $this->bck = new Bck($this->currentPath);
        
        echo 'BCK version: ' . Bck::VERSION . "\n" .
             'current path: ' . $path . "\n";
    }
    
    
    /**
     * Select current job and run it. If no job is found shows help message.
     * 
     * @access public
     * @return void
     */
    public function run()
    {
        $method = $this->args[1] . 'Job';
        if ($this->args[1] AND method_exists($this, $method))
            return $this->$method();
        else
            return $this->helpMessage();
    }
    
    
    /**
     * Show help message to user.
     * 
     * @access public
     * @return void
     */
    public function helpMessage()
    {
        $reflection = new \ReflectionClass($this);
        echo "Usage of bck:\n";
        foreach ($reflection->getMethods() as $method) {
            if (($pos = strpos($method->name, 'Job')) === false)
                continue;
            
            $jobName = substr($method->name, 0, $pos);
            echo "\033[1m$jobName\033[0m - ";
            $comment = $method->getDocComment();
            
            $pos   = strpos($comment, '@helpMessage');
            $start = $pos + strlen('@helpMessage');
            $end   = strpos($comment, "\n", $start);
            echo substr($comment, $start, $end - $start) . "\n";
        }
        return 0;
    }
    
    public function getRunTime()
    {
        return microtime(true) - $this->runtime;
    }
    
    
    /**
     * Run init command
     * 
     * @helpMessage Init bck to project.
     * @access public
     * @return void
     */
    public function initJob()
    {
        return $this->bck->init();
    }
    
    /**
     * Run clean command
     * 
     * @helpMessage clean project from bck
     * @access public
     * @return void
     */
    public function cleanJob()
    {
        echo "It will destroy backup service. Are you sure? (Y/n)";
        do {
            $answer = trim(fgets(STDIN));
            if ($answer === 'Y') {
                $this->bck->clean();
                break;
            }
            elseif ($answer === 'n')
                break;
        }
        while (1);
    }
    
    /**
     * configJob function.
     * 
     * @helpMessage bck config <option> <value> | bck config global <option> <value>
     * @access public
     * @return void
     */
    public function configJob()
    {
        $global = ($this->args[2] == 'global');
        $argOffset = (int)$global;
        
        $configKey   = $this->args[2 + $argOffset];
        $configValue = $this->args[3 + $argOffset];
        
        $config = $this->bck->getConfig($global);

        $writeMode = !!$configValue;
        
        if ($writeMode) {
            //$config->set($configKey, $configValue, $global);
            echo "do it by your hands ;)\n";
            
            //save to local or global
            //reload
            return 1;
        }
        
        if ($config->isseted($configKey)) {
            echo "Current value for key $configKey is: " . $config->get($configKey) . 
                ($global ? ' from global config' : '') . 
                "\n";
        } else
            echo "Value for key $configKey is not setted" .
                ($global ? ' in global config' : '') . 
                "\n";
        return 0;
    }
    
    
    /**
     * statJob function.
     * 
     * @helpMessage bck stat - show files which wiil be in next back up
     * @access public
     * @return void
     */
    public function statJob()
    {
        $subPath = $this->args[2];
        if ($subPath)
            $subPath = $this->bck->getPath() . '/' . $subPath;

        $count = 0;
        $this->bck->scan($subPath, function($file) use(&$count) {
            $count ++;
            //var_dump(memory_get_usage(true));
            echo $file;
    
            if ($file->isNew())
                echo "\t new file";
            elseif ($file->isModified())
                echo "\t modified";
            echo "\n";
        });
        
        if (!$count)
            echo "backup is up to date\n";
    }
    
    /**
     * updateJob function.
     * 
     * @helpMessage bck push - send files to ftp
     * @access public
     * @return void
     */
    public function pushJob()
    {
        $count = $failed = 0;
        $this->bck->push(function($file, $result = NULL) use (&$count) {
            if (is_null($result))
                echo 'Send file: ' . $file . "\n";
            else
                echo "\t" . ($result ? 'ok' : 'FAILED') . "\n";

            if ($result === true)
                $count ++;
            elseif ($result === false)
                $failed ++;
        });
        
        if ($count + $failed) {
            echo "Backuped " . (int)$count . " files\n";
            echo "Failed " . (int)$failed . " files\n";
        } else
            echo "backup is up to date\n";
    }
}