<?php
namespace OrbitSpaceSoft\soft;

use OrbitSpaceSoft\model;
use OrbitSpaceSoft\helpers;

class User extends Lib
{
    protected $model_token;
    protected $tokens;

    public function getTokens()
    {
        return $this->tokens;
    }

    public function init()
    {
        $this->connector = new Connector($this->config);
        $this->model_token = (new model\TokenModel($this->config))->connect();
    }

    public function login( $login, $password, bool $add_token = true ):bool
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

        if( $add_token )
        {
            if( !$this->model_token->add( $res['token'], $res['token_refresh'], $login ) )
            {
                $this->last_error = helpers\Result::connectorErrorMessage( $this->model_token->last_error );
                return false;
            }
        }

        $this->tokens = ['token' => $res['token'], 'token_refresh' => $res['token_refresh']];
        return true;
    }

    public function addTokens( $token, $refresh, $login, $delete_old=true )
    {
        if( !$this->model_token->add( $token, $refresh, $login ) )
        {
            $this->last_error = helpers\Result::connectorErrorMessage( $this->model_token->last_error );
            return false;
        }

        if( $delete_old )
            $this->model_token->delete([ $this->model_token->getField('user') => $login ]);

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

    public function checkToken( $token=null )
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
        ) ? true : false;
    }

    public function refreshToken( $token, $refresh )
    {
        if( !$tk = $this->model_token->select([
            $this->model_token->getField('token') => $token,
            $this->model_token->getField('refresh') => $refresh,
            $this->model_token->getField('active') => 'Y',
        ]) )
        {
            $this->last_error = 'token not found';
            return false;
        }

        if( !$tokens = $this->connector->result(
            $this->connector->connect(
                ($this->connector)::METHOD_POST,
                [],
                $this->config->application['domain'].'/common/user/refreshtoken/',
                [
                    ($this->connector)::TOKEN_SPACE_ID => $this->config->application['id'],
                    ($this->connector)::TOKEN_AUTH => $token,
                    ($this->connector)::TOKEN_REFRESH => $refresh
                ]
            )
        ) )
        {
            $this->last_error = helpers\Result::connectorErrorMessage($this->connector->response_last_error);
            return false;
        }

        $this->model_token->delete([ $this->model_token->getField('token') => $token ]);
        if( !$this->model_token->add( $token, $refresh, $tk[0]['user'] ) )
        {
            $this->last_error = helpers\Result::connectorErrorMessage( $this->model_token->last_error );
            return false;
        }

        $this->tokens = ['token' => $tokens['token'], 'token_refresh' => $tokens['token_refresh']];
        return true;
    }

    public function find( array $params )
    {
        return $this->request($params, '/common/user/find/');
    }

    public function findByToken( array $params )
    {
        return $this->request($params, '/common/user/findbytoken/');
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

    public function validatePassword( array $params )
    {
        return $this->request($params, '/common/user/validatepassword/');
    }

    public function logout()
    {
        return $this->request([], '/common/user/logout/');
    }

    public function updateProperty( array $params )
    {
        return $this->request($params, '/common/user/updateproperty/');
    }

    public function updatePropertyByID( array $params )
    {
        return $this->request($params, '/common/user/updatepropertybyid/');
    }

    public function getListProperty( array $params )
    {
        return $this->request($params, '/common/user/getlistproperty/');
    }
}