<?php

namespace Woda\CmsBundle\Service;

use Woda\AdminBundle\Component\Menu\AbstractMenu;

class Menu extends AbstractMenu
{
    public function __construct($router)
    {
        $this->options['label'] = 'wodacmsbundle.menu.title';

        $this->addLink('wodacmsbundle.menu.new', $router->generate('woda_cms_cms_new'), array('icon' => 'icon-ok'));
        $this->addLink('wodacmsbundle.menu.list', $router->generate('woda_cms_cms_list'), array('icon' => 'icon-file'));
    }
}
