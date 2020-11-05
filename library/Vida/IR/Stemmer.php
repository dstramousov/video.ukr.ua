<?php

class Vida_IR_Stemmer
{
    protected $_stemmer;
    
    /**
     * Возвращает объект штеммера
     * 
     * @return RussianStemmer
     */
    protected function getStemmer()
    {
        if (null === $this->_stemmer) {
            require_once 'Vida/IR/RussianStemmer.php';
            $this->_stemmer = new RussianStemmer();
        }
        return $this->_stemmer;
    }
    
    /**
     * Возвращает неизменяемую часть слова
     * 
     * @return RussianStemmer
     */
    public function stemWord($word)
    {
        $stemmer = $this->getStemmer();
        return $stemmer->stem_word($word);
    }
}

