<?php
namespace OrbitSpaceSoft;

class BaseObject
{
    public $config;
    public $last_error;

    public function __construct( \OrbitSpaceSoft\soft\Config $config )
    {
        $this->config = $config;
        $this->init();
    }

    /**
     * Инициализация объекта после конструктора
     */
    public function init(){}
}