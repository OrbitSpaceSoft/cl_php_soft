<?php
namespace OrbitSpaceSoft\soft;

class Notification extends Lib
{
    public function callEvent( $id, array $where=[] )
    {
        return $this->request(
            [
                'id' => $id,
                'params' => $where
            ],
            '/common/notification/callevent/'
        );
    }

    public function sendEmailTemplate( string $code, array $where=[])
    {
        return $this->request(
            [
                'code' => $code,
                'params' => $where
            ],
            '/common/notification/sendemailtemplate/'
        );
    }

}