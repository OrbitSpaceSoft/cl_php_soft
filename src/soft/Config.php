<?php
namespace OrbitSpaceSoft\soft;

class Config
{
    public $application;
    public $last_error;

    public function __construct( array $config )
    {
        if( !count($config) ) {
            $this->last_error = 'empty config array';
            return;
        }

        foreach ( $config as $type => $params )
            $this->{$type} = $params;
    }
}