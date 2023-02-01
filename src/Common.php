<?php
namespace OrbitSpaceSoft;

use OrbitSpaceSoft\soft;
use OrbitSpaceSoft\install;

class Common
{
    public $config;
    public $connector;
    public $user;
    public $iboxes;
    public $notification;
    public $space;
    public $uploader;

    public $last_error;
    public $installer;


    protected $token;
    protected $token_refresh;

    public function __construct( array $config )
    {
        $this->config = new soft\Config($config);
        $this->connector = new soft\Connector($this->config);

        $this->user = new soft\User($this->config);
        $this->iboxes = new soft\IBoxes($this->config);
        $this->notification = new soft\Notification($this->config);
        $this->space = new soft\Space($this->config);
        $this->uploader = new soft\Uploader($this->config);

        $this->installer = new install\Installer($this->config);
    }

    public function debug($flag=false)
    {
        $this->connector->debug = $flag;
        $this->user->debug($flag);
        $this->iboxes->debug($flag);
        $this->notification->debug($flag);
        $this->space->debug($flag);
        $this->uploader->debug($flag);

        return $this;
    }

    public function setToken($token,$token_refresh)
    {
        $this->user->setToken($token);
        $this->iboxes->setToken($token);
        $this->notification->setToken($token);
        $this->space->setToken($token);
        $this->uploader->setToken($token);

        $this->token = $token;
        $this->token_refresh = $token_refresh;

        return $this;
    }

    public function ping( $token=null )
    {
        $token = is_null($token) ? $this->token : $token;

        return $this->connector->result(
            $this->connector->connect(
                ($this->connector)::METHOD_POST,
                [],
                $this->config->application['domain'].'/common/ping/',
                [
                    ($this->connector)::TOKEN_SPACE_ID => $this->config->application['id'],
                    ($this->connector)::TOKEN_AUTH => $token
                ]
            )
        ) !== false;
    }

    public function request( array $where, string $link)
    {
        $res = $this->connector->result(
            $this->connector->connect(
                ($this->connector)::METHOD_POST,
                $where,
                $this->config->application['domain'].$link,
                [
                    ($this->connector)::TOKEN_SPACE_ID => $this->config->application['id'],
                    ($this->connector)::TOKEN_AUTH => $this->token
                ]
            )
        );

        if( !$res )
        {
            $this->last_error = helpers\Result::connectorErrorMessage( $this->connector->response_last_error );
            return false;
        }

        return $res;
    }
}