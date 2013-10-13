<?php

namespace Woda\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * Helper controller.
 *
 */
class HelperController extends Controller
{
    public function getDownloadLinksAction()
    {
        $router = $this->get('router');
        $collectionRoute = $router->getRouteCollection();

        return (new Response($collectionRoute->get('WodaFSBundle.Default.download')->getPattern()));
    }
}
