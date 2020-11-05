<?php

class Vida_Smarty 
{
  protected $_smarty;

  public function __construct() 
  {
    include 'Smarty/libs/Smarty.class.php';
    $this->_smarty = new Smarty();
    $this->_smarty->debugging = false;
//    $this->_smarty->force_compile = true;
    $this->_smarty->caching = false;
    $this->_smarty->compile_check = true;
    $this->_smarty->cache_lifetime = -1;
    $this->_smarty->template_dir = APPLICATION_PATH . '/../resources/templates';
    $this->_smarty->compile_dir = APPLICATION_PATH . '/../resources/templates_c';
    //$this->_smarty->plugins_dir = array( SMARTY_DIR . 'plugins', 'resources/plugins');
    $this->_smarty->plugins_dir = array( APPLICATION_PATH . '/../library/Smarty/libs/' . 'plugins');
    
  }

  /**
   *
   *
   */
  public function getSmarty() {
    return $this->_smarty;
  }
}