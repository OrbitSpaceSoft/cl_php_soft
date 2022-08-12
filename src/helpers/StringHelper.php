<?php
namespace OrbitSpaceSoft\helpers;

class StringHelper
{
    public static function translit($s) {
        $s = (string) $s;
        $s = trim($s);
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s);
        $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>'',' '=>'_','-'=>'_') );
        //$s = strtr($s, array('a'=>'a','b'=>'b','v'=>'v','g'=>'g','d'=>'d','e'=>'e','e'=>'e','j'=>'j','z'=>'z','i'=>'i','y'=>'y','k'=>'k','l'=>'l','m'=>'m','n'=>'n','o'=>'o','p'=>'p','r'=>'r','s'=>'s','t'=>'t','u'=>'u','f'=>'f','h'=>'h','c'=>'c','ch'=>'ch','sh'=>'sh','shch'=>'shch', 'y'=>'y', 'e'=>'e','yu'=>'yu','ya'=>'ya',''=>'',''=>'',' '=>'_','-'=>'_') );
        $s = preg_replace("/[^a-z_0-9]/i", "", $s);
        return $s;
    }

    public static function trimString($string, $length=32)
    {
        switch ($string)
        {
            case mb_strlen($string) > $length:
                return substr($string, 0, $length);
            case mb_strlen($string) == $length:
                return $string;
            case mb_strlen($string) < $length:
            default:
                return false;
        }
    }

    public static function formatDate( $date, $f='d.m.Y' )
    {
        return (new \DateTime($date))->format($f);
    }

    public static function trace( $array=[] )
    {
        return "<pre>".print_r($array,true)."</pre>";
    }
}