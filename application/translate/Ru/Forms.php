<?php
return array(
    Zend_Validate_Alnum::NOT_ALNUM                  => '��������� �������� "%value%" ������������. ��������� ������ ��������� ������� � �����',
    Zend_Validate_Alnum::STRING_EMPTY               => '���� �� ����� ���� ������. ��������� ���, ����������',
    
    Zend_Validate_Alpha::NOT_ALPHA                  => '������� � ��� ���� ������ ��������� �������',
    Zend_Validate_Alpha::STRING_EMPTY               => '���� �� ����� ���� ������. ��������� ���, ����������',
    
    Zend_Validate_Barcode_UpcA::INVALID             => '"%value% ������������ UPC-A �����-���"',
    Zend_Validate_Barcode_UpcA::INVALID_LENGTH      => '������������ �������� "%value%". ������� 12 ��������',
    
    Zend_Validate_Barcode_Ean13::INVALID            => '"%value% ������������ EAN-13 �����-���',
    Zend_Validate_Barcode_Ean13::INVALID_LENGTH     => '������������ �������� "%value%". ������� 13 ��������',
    
    Zend_Validate_Between::NOT_BETWEEN              => '"%value%" �� ��������� ����� "%min%" � "%max%", ������������',
    Zend_Validate_Between::NOT_BETWEEN_STRICT       => '"%value%" �� ��������� ������ ����� "%min%" � "%max%"',
    
    Zend_Validate_Ccnum::LENGTH                     => '"%value%" ������ ���� ��������� ��������� �� 13 �� 19 ���� �������',
    Zend_Validate_Ccnum::CHECKSUM                   => '������� ����������� ����� ��������. �������� "%value%" �������',
    
    Zend_Validate_Date::NOT_YYYY_MM_DD              => '"%value%" �� �������� ��� ������ ���-�����-����(����. 2008-11-03)',
    Zend_Validate_Date::INVALID                     => '"%value%" - �������� ����',
    Zend_Validate_Date::FALSEFORMAT                 => '"%value%" - �� �������� �� �������',
    
    Zend_Validate_Digits::NOT_DIGITS                => '�������� "%value%" ������������. ������� ������ �����',
    Zend_Validate_Digits::STRING_EMPTY              => '���� �� ����� ���� ������. ��������� ���, ����������',
    
    Zend_Validate_EmailAddress::INVALID             => '"%value%" ������������ ����� ����������� �����. ������� ��� � ������� ���@�����',
    Zend_Validate_EmailAddress::INVALID_HOSTNAME    => '"%hostname%" �������� ����� ��� ������ "%value%"',
    Zend_Validate_EmailAddress::INVALID_MX_RECORD   => '����� "%hostname%" �� ����� MX-������ �� ������ "%value%"',
    Zend_Validate_EmailAddress::DOT_ATOM            => '"%localPart%" �� ������������� ������� dot-atom',
    Zend_Validate_EmailAddress::QUOTED_STRING       => '"%localPart%" �� ������������� ������� ��������� ������',
    Zend_Validate_EmailAddress::INVALID_LOCAL_PART  => '"%localPart%" �� ���������� ��� ��� ������ "%value%", ������� ����� ���� ���@�����',
    
    Zend_Validate_Float::NOT_FLOAT                  => '"%value%" �� �������� ������� ������',
    
    Zend_Validate_GreaterThan::NOT_GREATER          => '"%value%" �� �������� ������� �� "%min%"',
    
    Zend_Validate_Hex::NOT_HEX                      => '"%value%" ������ � ���� �� ������ ����������������� �������',
    
    Zend_Validate_Hostname::IP_ADDRESS_NOT_ALLOWED  => '"%value%" - ��� IP-�����, �� IP-������ �� ��������� ',
    Zend_Validate_Hostname::UNKNOWN_TLD             => '"%value%" - ��� DNS ��� �����, �� ��� �� ����� ���� �� TLD-������',
    Zend_Validate_Hostname::INVALID_DASH            => '"%value%" - ��� DNS ��� �����, �� ���� "-" ��������� � ������������ �����',
    Zend_Validate_Hostname::INVALID_HOSTNAME_SCHEMA => '"%value%" - ��� DNS ��� �����, �� ��� �� ������������� TLD ��� TLD "%tld%"',
    Zend_Validate_Hostname::UNDECIPHERABLE_TLD      => '"%value%" - ��� DNS ��� �����. �� ������ ������� TLD �����',
    Zend_Validate_Hostname::INVALID_HOSTNAME        => '"%value%" - �� ������������� ��������� ��������� ��� DNS ����� �����',
    Zend_Validate_Hostname::INVALID_LOCAL_NAME      => '"%value%" - ����� �������� ������������ ������� ��������� ���������',
    Zend_Validate_Hostname::LOCAL_NAME_NOT_ALLOWED  => '"%value%" - ����� �������� ������� ��������� ���������, �� ������� �������� ��������� �� ���������',
    
    Zend_Validate_Identical::NOT_SAME               => '�������� �� ���������',
    Zend_Validate_Identical::MISSING_TOKEN          => '�� ���� ������� �������� ��� �������� �� ������������',
    
    Zend_Validate_InArray::NOT_IN_ARRAY             => '"%value%" �� ������� � ������������� ���������� ���������',
    
    Zend_Validate_Int::NOT_INT                      => '"%value%" �� �������� ������������� ���������',
    
    Zend_Validate_Ip::NOT_IP_ADDRESS                => '"%value%" �� �������� ���������� IP-�������',
    
    Zend_Validate_LessThan::NOT_LESS                => '"%value%" �� ������ ��� "%max%"',
    
    Zend_Validate_NotEmpty::IS_EMPTY                => '�������� ���� �� ������',
    
    //Zend_Validate_Regex::NOT_MATCH                  => '�������� "%value%" �� �������� ��� ������ ����������� ��������� "%pattern%"',
    
    Zend_Validate_StringLength::TOO_SHORT           => '������ ��������� �������� "%value%", ������ ��� %min% ����.',
    Zend_Validate_StringLength::TOO_LONG            => '������ ��������� �������� "%value%", ������ ��� %max% ����.',
    Zend_Captcha_Word::MISSING_VALUE                => '������� �� �������',
    Zend_Captcha_Word::MISSING_ID                   => '�� ������ ���� ID Captcha',
    Zend_Captcha_Word::BAD_CAPTCHA                  => '������� ������� �������',

);
