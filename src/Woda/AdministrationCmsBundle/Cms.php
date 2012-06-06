<?php

namespace Woda\AdministrationCmsBundle;

/*
 * Must be replace by cms entity !!!
 */

class Cms
{
    public $mName   =    '';

    public
    function __construct($name)
    {
        $this->mName = $name;
    }

    public
    function getName()
    {
        return ($this->mName);
    }
}