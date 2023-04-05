<?php
namespace OrbitSpaceSoft\soft;

use OrbitSpaceSoft\helpers;

class Connector extends \OrbitSpaceSoft\BaseObject
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';

    const STATUS_ERROR = 'ERROR';
    const STATUS_OK = 'OK';

    const TOKEN_USER_ID         = 'Orb-Uid';
    const TOKEN_AUTH            = 'Orb-Auth';
    const TOKEN_REFRESH         = 'Orb-Refresh';
    const TOKEN_SIMPLE          = 'Orb-Token';
    const TOKEN_SPACE_ID        = 'Orb-Spaceid';
    const TOKEN_SIMPLE_GET      = 'token';

    const DECRYPT_INCOMING_REQUEST = true;
    const ENCRYPT_OUTGOING_REQUEST = true;
    const DECRYPT_ERRORS = false;

    public $class_security = '\OrbitSpaceSoft\soft\Security';

    public $curl;
    public $response_last_error=false;
    public $api_domain;
    public $debug = false;
    public $debug_result= false;
    public $die = false;


    protected $hash=false;
    protected $security;

    protected $_defaultOptions = [
        CURLOPT_USERAGENT      => 'Orbit-Space-Curl-Agent',
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
    ];

    public function init()
    {
        $this->curl = new \Curl\Curl;
        $this->security = new $this->class_security($this->config);
        $this->hash = $this->security->spaceHash();
    }

    protected function beforeConnect()
    {
        $this->curl = new \Curl\Curl;
    }

    public function encryptString($string)
    {
        return $this->security->openSslEncrypt( $string, $this->hash );
    }

    public function decryptString($string)
    {
        return $this->security->openSslDecrypt( $string, $this->hash );
    }

    public function connect( $method, array $params, $link, array $headers, array $options=[] )
    {
        $this->beforeConnect();

        $_p = $params;

        if( (isset($this->config->application['encrypt_outgoing_request']) && $this->config->application['encrypt_outgoing_request'])
            || ( !isset($this->config->application['encrypt_outgoing_request']) && self::ENCRYPT_OUTGOING_REQUEST )
        ) {
            if( count($params) )
                $params = ['message' => $this->security->openSslEncrypt( json_encode($params), $this->hash )];

            if( count($headers) )
            {
                foreach ( $headers as $code => $value )
                {
                    if( in_array($code, [static::TOKEN_USER_ID, static::TOKEN_AUTH, static::TOKEN_REFRESH]) )
                        $headers[$code] = $this->security->openSslEncrypt( $value, $this->hash );
                }
            }
        }

        if( $this->debug )
            echo helpers\StringHelper::trace([
                'headers' => $headers,
                'params_before' => $_p,
                'params_after' => $params,
                'link' => $link,
                //'trace'=> debug_backtrace()
            ]);

        if( $this->die ) die();

        switch ($method)
        {
            case self::METHOD_GET:
                return $this->curl->get($link, $params);

            case self::METHOD_POST:

                if( is_array($headers) && count($headers) )
                    $this->curl->setHeaders( $headers );

                if( is_array($options) && count($options) )
                {
                    foreach ($options as $option => $value) {
                        $this->curl->setOpt( $option, $value );
                    }
                }

                $this->curl->post($link, $params);
                if( $this->curl->error )
                {
                    $message = ( isset($this->curl->response->error )
                        &&  isset($this->curl->response->error->message )
                            ? $this->curl->response->error->message
                            : $this->curl->errorMessage
                    );

                    $this->response_last_error = [
                        'code' => isset($this->curl->errorCode)?$this->curl->errorCode:500,
                        'message' => isset($message)?$message:'Error'
                    ];

                    $this->last_error = $this->curl->errorCode . ': ' .$message;
                    return $this->curl->response;
                }
                //var_dump($this->curl->response);
                return $this->curl->response;

            default: return false;
        }
    }

    public function result( $resp )
    {
        $resp = helpers\JsonHelper::object_to_array($resp);
        if( !isset($resp['status']) || $resp['status'] === self::STATUS_ERROR )
        {
            if( ( ( isset($this->config->application['decrypt_errors']) && $this->config->application['decrypt_errors']) )
                || ( !isset($this->config->application['decrypt_errors']) && self::DECRYPT_ERRORS ) )
                $this->response_last_error = $this->security->openSslDecrypt($resp['error'], $this->hash);
            else
                $this->response_last_error = isset($resp['error'])?$resp['error']:'Error';

            if( $this->debug_result )
                var_dump($this->response_last_error);

            return false;
        }

        if( (isset($this->config->application['decrypt_incoming_request']) && $this->config->application['decrypt_incoming_request']) )
            $_res = $this->security->openSslDecrypt($resp['data'], $this->hash);
        elseif ( !isset($this->config->application['decrypt_incoming_request']) && self::DECRYPT_INCOMING_REQUEST )
            $_res = $this->security->openSslDecrypt($resp['data'], $this->hash);
        else $_res = $resp['data'];

        $return = !is_array($_res) && helpers\JsonHelper::isJson( $_res ) ? helpers\JsonHelper::object_to_array( json_decode($_res) ) : $_res;
        if( $this->debug_result )
            echo helpers\StringHelper::trace($return);
        return $return;
    }

    public function createErrorMessage($code, $message)
    {
        return ['code' => $code, 'message' => $message];
    }
}