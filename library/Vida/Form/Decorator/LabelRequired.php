<?php

class Vida_Form_Decorator_LabelRequired extends Zend_Form_Decorator_Label
{
    /**
    *
    */
    public function getLabel()
    {
        $element = $this->getElement();
        $errors = $element->getMessages();
        
        $label = trim($element->getLabel());
        if (empty($errors))
        {
            if($element->isRequired()) {
                $label .= ' <strong class="required">'
                    . '*'
                    . '</strong>';
                $element->setLabel($label);
            }
        }
        else
        {
            $label .= ' <strong class="feedback">'
                . implode('</strong><br /><strong>', $errors)
                . '</strong>';
            $element->setLabel($label);
        }
        return parent::getLabel();
    }

}