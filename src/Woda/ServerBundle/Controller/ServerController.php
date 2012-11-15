<?php

namespace Woda\ServerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Server controller.
 *
 * @Route("/admin/server")
 */
class ServerController extends Controller
{
    /**
     * Lists all Cms entities.
     *
     * @Route("/show", name="woda_server_server_show")
     * @Template()
     */
    public function showAction()
    {
        return array('name' => 'toto');
    }
}
