<?php

class Vida_IR_Stemmer
{
    protected $_stemmer;
    
    /**
     * ���������� ������ ��������
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
     * ���������� ������������ ����� �����
     * 
     * @return RussianStemmer
     */
    public function stemWord($word)
    {
        $stemmer = $this->getStemmer();
        return $stemmer->stem_word($word);
    }
}

