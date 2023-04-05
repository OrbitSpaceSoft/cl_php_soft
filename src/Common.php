<?php
namespace OrbitSpaceSoft;

use OrbitSpaceSoft\soft;
use OrbitSpaceSoft\install;

class Common
{
    /**
     * @var soft\Config
     */
    public $config;
    /**
     * @var soft\Connector
     */
    public $connector;
    /**
     * @var soft\User
     */
    public $user;
    /**
     * @var soft\IBoxes
     */
    public $iboxes;
    /**
     * @var soft\Notification
     */
    public $notification;
    /**
     * @var soft\Space
     */
    public $space;
    /**
     * @var soft\Uploader
     */
    public $uploader;

    public $last_error;
    /**
     * @var install\Installer
     */
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

    /**
     * @param bool $flag
     * @return $this
     */
    public function debug($debug_result = false, $die=false)
    {
        $this->connector->debug = true;
        $this->connector->debug_result = $debug_result;
        $this->user->debug($debug_result, $die);
        $this->iboxes->debug($debug_result, $die);
        $this->notification->debug($debug_result, $die);
        $this->space->debug($debug_result, $die);
        $this->uploader->debug($debug_result, $die);

        return $this;
    }

    /**
     * @param $token
     * @param $token_refresh
     * @return $this
     */
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

    /**
     * @param null $token
     * @return bool
     */
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

    /**
     * @param array $where
     * @param string $link
     * @return array|bool
     */
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