<?php

namespace Woda\AdministrationCmsBundle;

abstract
class Collection
{
    protected $mData  =   array();

    public
    function push($data)
    {
        array_push($this->mData, $data);
    }

    public abstract function get($key);
}