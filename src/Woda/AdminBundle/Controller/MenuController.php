<?php

namespace Woda\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Woda\AdminBundle\Component\Menu\AbstractMenu;

class MenuController extends Controller
{
    public function masterAction()
    {
        $menu = $this->get('woda_admin.admin_menu');

        return $this->render('WodaAdminBundle:Menu:master.html.twig', array('root' => $menu));
    }

    public function elementAction($element)
    {
        if ($element instanceof AbstractMenu) {
            return $this->render('WodaAdminBundle:Menu:menu.html.twig', array('menu' => $element));
        } else {
            return $this->render('WodaAdminBundle:Menu:link.html.twig', array('link' => $element));
        }
    }
}
