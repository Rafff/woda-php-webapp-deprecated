<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentification\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormError;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Friends controller.
 *
 * @Route("/account/friends")
 */
class FriendsController extends Controller
{
    /**
     * @Route("/", name="WodaUserBundle.Friends.list")
     * @Template("WodaUserBundle:Friends:list.html.twig")
     */
    public function listAction()
    {
        return (array());
    }
}
