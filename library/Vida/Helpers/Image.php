<?php

class Vida_Helpers_Image
{
    public static function size($imgfile)
    {
       $res = array('width' => '0', 'height' => '0');
       if (function_exists("imagecreate")) {
           $imginfo = @getimagesize($imgfile);

           $res['width'] = $imginfo[0];
           $res['height'] = $imginfo[1];
       }
       return $res;
    }
}