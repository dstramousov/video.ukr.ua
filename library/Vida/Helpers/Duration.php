<?php

class Vida_Helpers_Duration
{
    protected $_start;
    protected $_end;
    
    public function start()
    {
        $this->_start = microtime(1);
    }

    public function end()
    {
        $this->_end = microtime(1);
    }
    
    public function toString()
    {
        return sprintf("%.3f sec", ( $this->_end - $this->_start ));
    }

}