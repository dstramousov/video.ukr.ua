<?php

class Vida_Helpers_LoginHelper
{
    /**
     * 
     * @param  string       $url 
     * @return void
     */
    public static function setReturnUrl($url)
    {
       $namespace = new Zend_Session_Namespace();
       $namespace->return_url = $url;
    }

    /**
     * 
     * @return string       
     */
    public static function getReturnUrl()
    {
       $namespace = new Zend_Session_Namespace();
       return $namespace->return_url;
    }

}