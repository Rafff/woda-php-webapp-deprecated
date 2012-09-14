<?php

namespace Woda\ContentBundle\Service;

use Woda\AdminBundle\Component\Menu\AbstractMenu;

class Menu extends AbstractMenu
{
    public function __construct($router)
    {
        $this->options['label'] = 'wodacontentbundle.menu.title';

        $this->addLink('wodacontentbundle.menu.list', $router->generate('WodaContentBundle.Admin.list'), array('icon' => 'icon-list'));
    }
}
