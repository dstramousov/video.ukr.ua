<?php

class Vida_Helpers_Page
{
    public static function getPage()
    {
        return  Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
    }    
    
    /**
    * ���������� true, ���� ������� �������� �������
    */
    public static function isMain()
    {
        return (self::getPage() == 'index');
    }

    public static function getView() {
        return Zend_Layout::getMvcInstance()->getView();
    }

}