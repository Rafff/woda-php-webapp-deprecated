<?php

namespace Woda\AdminBundle\Component\Menu;

class Link
{
    public $label;

    public $target;

    public $options;

    public function __construct($label, $target, array $options = array())
    {
        $this->label = $label;

        $this->target = $target;

        $this->options = $options;
    }
}
