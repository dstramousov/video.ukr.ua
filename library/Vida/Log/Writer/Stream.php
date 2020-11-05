<?php
class Vida_Log_Writer_Stream extends Zend_Log_Writer_Stream
{
    protected $_streamOrUrl = null;
    protected $_mode = null;
    protected $_closed = true;

    public function __construct($streamOrUrl, $mode = 'a')
    {
        parent::__construct($streamOrUrl, $mode);

        $this->_streamOrUrl = $streamOrUrl;
        $this->_mode = $mode;

        $this->_close();
    }

    protected function _open() {
        if($this->_closed) {
            parent::__construct($this->_streamOrUrl, $this->_mode);
            $this->_closed = false;
        }
    }

    protected function _close() {
        if(!$this->_closed) {
            $this->shutdown();
            $this->_closed = true;
        }
    }

    protected function _write($event)
    {
        $this->_open();
        parent::_write($event);
        $this->_close();
    }

}
