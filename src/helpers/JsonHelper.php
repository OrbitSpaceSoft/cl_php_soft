<?php
namespace OrbitSpaceSoft\helpers;

class JsonHelper
{
    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function object_to_array($data) {
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = self::object_to_array($value);
            } return $result;
        } return $data;
    }
}