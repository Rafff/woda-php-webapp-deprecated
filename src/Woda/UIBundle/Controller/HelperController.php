<?php

namespace Woda\UIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Helper controller.
 *
 */
class HelperController extends Controller
{
    public function getUserNameAction()
    {
        return new Response($this->get('security.context')->getToken()->getUser()->getUserName());
    }
}
