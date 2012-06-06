<?php

namespace Woda\AdministrationCmsBundle;

class CmsCollection extends Collection
{
    public
    function __construct($arrayCms = array())
    {
        if (is_array($arrayCms)) {
            foreach ($arrayCms as $cms) {
                $this->push($cms);
            }
        }
    }

    public
    function push($cms)
    {
        if (!($cms instanceof \Woda\AdministrationCmsBundle\Entity\Cms)) {
            throw new \Exception("You must insert an instance of \Woda\AdministrationCmsBundle\Cms");
        }

        parent::push($cms);
    }

    public
    function get($name = null)
    {
        if ($name === null) {
            return ($this->mData);
        }

        foreach ($this->mData as $cms) {
            if ($cms->mName == $name) {
                return ($cms);
            }
        }

        return (null);
    }
}