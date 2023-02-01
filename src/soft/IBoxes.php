<?php
namespace OrbitSpaceSoft\soft;

class IBoxes extends Lib
{
    public function getProperty( array $where )
    {
        return $this->request($where, '/common/iboxes/getproperty/');
    }

    public function getPropertyFilter( array $where )
    {
        return $this->request($where, '/common/iboxes/getpropertyfilter/');
    }

    public function getElements( array $where )
    {
        return $this->request($where, '/common/iboxes/getelements/');
    }
    public function getElement(array $where)
    {
        return $this->request($where, '/common/iboxes/getelement/');
    }

    public function existElement( array $where )
    {
        $res = $this->request($where, '/common/iboxes/existelement/');
        return !$res ? false : $res['status'] === 'Y';
    }

    public function addElement( array $where )
    {
        return $this->request($where, '/common/iboxes/addelement/');
    }

    public function removeElement( array $where )
    {
        return $this->request($where, '/common/iboxes/removeelement/');
    }

    public function updateElement( array $where )
    {
        return $this->request($where, '/common/iboxes/updateelement/');
    }
}