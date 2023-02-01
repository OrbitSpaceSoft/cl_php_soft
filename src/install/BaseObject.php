<?php
namespace OrbitSpaceSoft\install;

use OrbitSpaceSoft\soft;

class BaseObject extends \OrbitSpaceSoft\BaseObject
{
    public $connector;
    protected $log = [];

    protected $log_name = 'common';

    public function init()
    {
        $this->connector = new soft\Connector($this->config);
    }

    public function debug($flag=false)
    {
        $this->connector->debug = $flag;
        return $this;
    }

    public function writeLog(string $name, $message )
    {
        $this->log[] = [
            'name' => $name,
            'message' => $message,
            'time' => date('Y-m-d H:i:s'),
        ];
    }

    public function createLog()
    {
        return file_put_contents(
            __DIR__.'/'.$this->log_name.'.log',
            print_r($this->log,true),
            FILE_APPEND | LOCK_EX
        );
    }

    public function checkLog():bool
    {
        return file_exists(__DIR__.'/'.$this->log_name.'.log');
    }
}