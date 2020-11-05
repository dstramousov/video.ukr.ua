<?php

class Vida_Helpers_SessionHelper
{
    /**
     * 
     * @param  string       $name 
     * @return void
     */
    public static function setParam($name, $value)
    {
       $namespace = new Zend_Session_Namespace();
       $namespace->$name = $value;
    }

    /**
     * 
     * @return string       
     */
    public static function getParam($name)
    {
       $namespace = new Zend_Session_Namespace();
       return $namespace->$name;
    }
}