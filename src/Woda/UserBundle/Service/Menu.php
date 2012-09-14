<?php

namespace Woda\UserBundle\Service;

use Woda\AdminBundle\Component\Menu\AbstractMenu;

class Menu extends AbstractMenu
{
    public function __construct($router)
    {
        $this->options['label'] = 'wodauserbundle.menu.title';

        $this->addLink('wodauserbundle.menu.list', $router->generate('WodaUserBundle.Admin.list'), array('icon' => 'icon-user'));
    }
}
