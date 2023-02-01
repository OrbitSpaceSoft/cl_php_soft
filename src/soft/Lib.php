<?php
namespace OrbitSpaceSoft\soft;

use OrbitSpaceSoft\helpers;

class Lib extends \OrbitSpaceSoft\BaseObject
{
    protected $connector;
    protected $token;

    public function init()
    {
        $this->connector = new Connector($this->config);
    }

    public function debug($flag=false)
    {
        $this->connector->debug = $flag;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token;
    }

    protected function request( array $where, string $link )
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