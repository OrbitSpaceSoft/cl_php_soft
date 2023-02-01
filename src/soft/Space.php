<?php
namespace OrbitSpaceSoft\soft;

class Space extends Lib
{
    public function getList( array $where )
    {
        return $this->request($where, '/common/space/getlist/');
    }
}