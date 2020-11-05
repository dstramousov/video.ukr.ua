<?php

/*
 * Базовый класс контроллеров действий
 *
 */
class Vida_Controller_Action extends Zend_Controller_Action
{
    public function preDispatch()
    {
        $this->view->headTitle()->SetSeparator(' :: ');
        parent::preDispatch();
    }    


    public function postDispatch()
    {
        parent::postDispatch();
        
        $this->view->headTitle('Video.ukr.ua');
    }

}