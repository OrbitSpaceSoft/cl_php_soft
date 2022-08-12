<?php
namespace OrbitSpaceSoft\soft;

use OrbitSpaceSoft\model;
use OrbitSpaceSoft\helpers;

class User extends Lib
{
    protected $model_token;

    public function init()
    {
        $this->connector = new Connector($this->config);
        $this->model_token = (new model\TokenModel($this->config))->connect();
    }

    public function login( $login, $password )
    {
        if( !$res = $this->connector->result(
            $this->connector->connect(
                ($this->connector)::METHOD_POST,
                ['password' => $password,'login' => $login],
                $this->config->application['domain'].'/common/user/authentication/',
                [
                    ($this->connector)::TOKEN_SPACE_ID => $this->config->application['id']
                ]
            )
        ) )
        {
            $this->last_error = helpers\Result::connectorErrorMessage($this->connector->response_last_error);
            return false;
        }

        $this->model_token->delete([ $this->model_token->getField('user') => $login ]);

        if( !$this->model_token->add( $res['token'], $res['token_refresh'], $login ) )
        {
            $this->last_error = helpers\Result::connectorErrorMessage( $this->model_token->last_error );
            return false;
        }

        return true;
    }

    public function getTokenByLogin( $login )
    {
        if( !$tokens = $this->model_token->select([
            $this->model_token->getField('user') => $login,
            $this->model_token->getField('active') => 'Y',
        ], 1) )
            return false;

        return [
            'token' => $tokens[0][ $this->model_token->getField('token') ],
            'refresh' => $tokens[0][ $this->model_token->getField('refresh') ],
        ];
    }

    public function checkTokenByLogin( $login )
    {
        if( !$tokens = $this->model_token->select([
            $this->model_token->getField('user') => $login,
            $this->model_token->getField('active') => 'Y',
        ], 1) )
            return false;

        return $this->connector->result(
            $this->connector->connect(
                ($this->connector)::METHOD_POST,
                [],
                $this->config->application['domain'].'/common/ping/',
                [
                    ($this->connector)::TOKEN_SPACE_ID => $this->config->application['id'],
                    ($this->connector)::TOKEN_AUTH => $tokens[0][ $this->model_token->getField('token') ]
                ]
            )
        ) ? true : false;
    }

    public function find( array $params )
    {
        return $this->request($params, '/common/user/find/');
    }

    public function add( array $params )
    {
        return $this->request($params, '/common/user/add/');
    }

    public function verificationEmailCode( array $params )
    {
        return $this->request($params, '/common/user/verificationemailcode/');
    }

    public function createEmailVerificationCode( array $params )
    {
        return $this->request($params, '/common/user/emailverificationcode/');
    }

    public function changeEmail( array $params )
    {
        return $this->request($params, '/common/user/changeemail/');
    }

    public function changePassword( array $params )
    {
        return $this->request($params, '/common/user/changepassword/');
    }
}