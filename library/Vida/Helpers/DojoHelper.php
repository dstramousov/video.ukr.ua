<?php

class Vida_Helpers_DojoHelper
{
    /**
     * ���������� ��ப� �� utf-8 � ����஢�� ��襣� ᠩ�
     * @param  string       $val  ��ப� ������ ����室��� �८�ࠧ�����
     * @return string       �८�ࠧ������� ��ப�
     */
    public static function convert($val)
    {
        return iconv("windows-1251", "utf-8", $val);
    }
}