<?php

class Vida_Data_SearchStat
{
    protected $_duration;       //����������������� ������
    protected $_total_count;    //������� ����� �������
    protected $_keys;           //�����, ���������� �� ������ ������

    /**
     * ����������� ������. �������� ��� �������� ����������� ���������� ������
     *
     */
    public function SearchStat()
    {
        $this->_keys = array();
        $this->_total_count = 0;
        $this->_duration = 0;
    }
    
    public function setDuration($value)
    {
        $this->_duration = $value;
    }
    public function getDuration()
    {
        return $this->_duration;
    }

    public function setTotalCount($value)
    {
        $this->_total_count = $value;
    }
    public function getTotalCount()
    {
        return $this->_total_count;
    }

    public function setKeys($value)
    {
        $this->_keys = $value;
    }
    public function getKeys()
    {
        return $this->_keys;
    }
    
}
