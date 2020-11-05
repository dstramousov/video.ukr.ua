<?php

class Vida_Paginator_Adapter_DbSelect extends Zend_Paginator_Adapter_DbSelect
{
    /**
    * Метод, возвращающий элементы для пейджера страниц
    * @param integer $offset
    * $param integer $itemCountPerPage
    * return array
    */
    public function getItems($offset, $itemCountPerPage)
    {
        $this->_select->limit($itemCountPerPage, $offset);
        return Vida_Helpers_DB::fetchAll(null, $this->_select);
    }

}

