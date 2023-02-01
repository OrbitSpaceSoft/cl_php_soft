<?php
namespace OrbitSpaceSoft\soft;

class Uploader extends Lib
{
    public function viewFile( array $where )
    {
        return $this->request($where, '/common/uploader/file/');
    }

    public function getFileUrl( array $where )
    {
        return $this->request($where, '/common/uploader/getfileurl/');
    }

    public function getFileInfo( array $where )
    {
        return $this->request($where, '/common/uploader/getfileinfo/');
    }
}