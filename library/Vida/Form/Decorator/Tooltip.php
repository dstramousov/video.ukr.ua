<?php

class Vida_Form_Decorator_Tooltip extends Zend_Form_Decorator_Description {

    /**
     * Render a description
     * 
     * @param  string $content 
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $description = $element->getDescription();
        $description = trim($description);

        if (empty($description)) {
            return $content;
        }
        
        $options = array();
        $options['tag'] = 'a';
        $options['href'] = 'javascript:void(0);';
        $options['id'] = 'tooltip_' . $element->getName();
        $options['class'] = 'tooltip_anchor';
        $options['disabled'] = "disabled";
        $placement = $this->getPlacement();
        //$separator = $this->getSeparator();
        $separator = '';
        
        $decorator = new Zend_Form_Decorator_HtmlTag($options);
        $html = $decorator->render('?');
        
        $html .= $view->inlineScript(Zend_View_Helper_HeadScript::SCRIPT,
            "new Tip('". $options['id']. "', \"" . $description . "\",{ style: 'protogray', border: 1, radius: 1, width: 200 });"
        );
        
        $description = $html;
        
        switch ($placement) {
            case self::PREPEND:
                return $description . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $description;
        }

    }
}
