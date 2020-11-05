<?php
class Vida_Form extends Zend_Form
{
    const CREATE = 1;   //режим создания новой записи
    const EDIT = 2;     //режим редактирования записи
    const FILTER = 3;   //режим поиска записей
   
    protected $_mode;
    protected $_id;
    
    public function setMode($mode)
    {
        $this->_mode = $mode;
    }
    
    protected $_standardElementDecorator = array(
        'ViewHelper',
        array('LabelRequired', array('escape' => false)),
        array('Tooltip', array('escape' => false, 'placement' => 'APPEND')),
        array('HtmlTag', array('tag'=>'p'))
    );
    
    protected $_standardGroupDecorator = array(
        'FormElements',
        'Fieldset'
    );

    protected $_buttonElementDecorator = array(
        'ViewHelper'
    );
    
    protected $_buttonGroupDecorator = array(
        'FormElements',
        'Fieldset'
    );
    
    protected $_noElementDecorator = array(
        'ViewHelper'
    );
    
    /**
     * Constructor
     *
     * Registers form view helper as decorator
     * 
     * @param mixed $options 
     * @return void
     */
    public function __construct($options = null)
    {
        $this->_setupTranslation();

        $this->_id = null;
        $this->_mode = self::CREATE;
        
        // Path setting for custom decorations MUST ALWAYS be first!
        $this->addElementPrefixPath('Vida_Form_Decorator', APPLICATION_PATH . '/../library/Vida/Form/Decorator', Zend_Form::DECORATOR);
        
        parent::__construct($options);
        
        $this->setDecorators(array(
            'FormElements',
            'Form',
        ));
        
        //установить режим работы формы
        if(is_array($options)) {
            if(array_key_exists('mode', $options)) {
                $this->_mode = $options['mode'];
            }
            if(array_key_exists('id', $options)) {
                $this->_id = $options['id'];
            }
            if(array_key_exists('action', $options)) {
                $this->setAction($options['action']);
            }
        }
        
        // What entry id are we editing?!
        if($this->_id != null) {
            $id = $this->createElement('hidden', 'id', array(
                'decorators' => $this->_noElementDecorator,
                'validators' => array(
                    array('regex', true,
                        array('/^([-]?\d{1,16})$/i',
                              'messages' => array(Zend_Validate_Regex::NOT_MATCH => 'Задано недопустимое значение Id')
                        )
                    )
                ),
                'required' => true
            ));
            $id->setValue($this->_id);
            $this->addElement($id);
        }
        
    }

    protected function _setupTranslation()
    {
        if (self::getDefaultTranslator()) {
            return;
        }
        $translate = new Zend_Translate('array', APPLICATION_PATH . '/translate/Ru/Forms.php', 'ru');
        Zend_Form::setDefaultTranslator($translate);
    }

}
