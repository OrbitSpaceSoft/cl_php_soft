<?
namespace OrbitSpaceSoft\soft;

class Security extends \OrbitSpaceSoft\BaseObject
{
    public $algo_open_ssl = 'aes-256-cbc';

    public function init()
    {
        $this->algo_open_ssl = isset( $this->config->application['algo_open_ssl'] )
            ? $this->config->application['algo_open_ssl']
            : $this->algo_open_ssl;
    }

    public function hashSting( $text, $length = 32, $algo = 'sha256' )
    {
        return substr( hash( $algo, $text, true ), 0, 32 );
    }

    public function shuffleString( $length = 32 )
    {
        return substr(str_shuffle( '0123456789abcdefghijklmnopqrstuvwxyz' ), 0, $length);
    }

    public function openSslEncrypt( $message, $password, $algo=null, $iv=null, $options=0 )
    {
        $algo = is_null($algo) ? $this->algo_open_ssl : $algo;
        $iv = is_null($iv) ? substr($password, 0, 16) : $iv;
        return base64_encode(openssl_encrypt($message, $algo, $password, $options, $iv));
    }

    public function openSslDecrypt( $message, $password, $algo=null, $iv=null, $options=0 )
    {
        $algo = is_null($algo) ? $this->algo_open_ssl : $algo;
        $iv = is_null($iv) ? substr($password, 0, 16) : $iv;
        return openssl_decrypt(base64_decode( $message ), $algo, $password, $options, $iv);
    }

    public function spaceHash()
    {
        if( !isset($this->config->application['key']) )
            return false;

        return $this->openSslEncrypt( $this->config->application['key'].$this->hashSalt($this->config->application['key']), $this->config->application['key'] );
    }

    private function hashSalt($string)
    {
        return \OrbitSpaceSoft\helpers\StringHelper::trimString($string,16);
    }
}