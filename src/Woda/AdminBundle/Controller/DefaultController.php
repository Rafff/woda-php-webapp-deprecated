<?php

namespace Woda\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/admin/", name="WodaAdminBundle.Default.index")
     * @Template("WodaAdminBundle::index.html.twig")
     */
    public function indexAction()
    {
        return array(
        );
    }
}
