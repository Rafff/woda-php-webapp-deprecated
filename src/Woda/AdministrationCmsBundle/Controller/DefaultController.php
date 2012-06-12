<?php

namespace Woda\AdministrationCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WodaAdministrationCmsBundle:Default:index.html.twig', array('name' => $name));
    }
}
