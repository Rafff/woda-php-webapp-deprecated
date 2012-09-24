<?php

namespace Woda\ServerBundle\Service;

use Woda\AdminBundle\Component\Menu\AbstractMenu;

class Menu extends AbstractMenu
{
    public function __construct($router)
    {
        $this->options['label'] = 'wodaserverbundle.menu.title';

        $this->addLink('wodaserverbundle.menu.link', $router->generate('woda_server_server_show'), array('icon' => 'icon-globe'));
    }
}
