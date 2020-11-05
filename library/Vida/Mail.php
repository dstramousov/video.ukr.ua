<?php

class Vida_Mail extends Zend_Mail
{
    /**
     * 
     */
    protected function _encodeHeader($value)
    {
      if (Zend_Mime::isPrintable($value)) {
          return $value;
      } else {
          $quotedValue = Zend_Mime::encodeBase64($value);
          return '=?' . $this->_charset . '?B?' . $quotedValue . '?=';
      }
    }

}