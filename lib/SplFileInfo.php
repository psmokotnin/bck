<?php

namespace Bck;

class SplFileInfo extends \SplFileInfo
{
    public function isDot()
    {
        return ($this->getFilename() == '.' OR $this->getFilename() == '..');
    }
}