<?php
namespace OrbitSpaceSoft\helpers;

class Result
{
    public static function connectorErrorMessage( $array=[] )
    {
        return 'OrbitSpace Error: #'.(isset($array['code'])?$array['code']:500).' - '.(isset($array['message'])?$array['message']:'Error');
    }
}