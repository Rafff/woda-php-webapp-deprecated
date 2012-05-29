<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentification\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProfileController extends Controller
{
    /**
     * @Route("/profile", name="WodaUserBundle.Profile.index")
     * @Template("WodaUserBundle:Profile:index.html.twig")
     */
    public function indexAction()
    {
        return array(
            'user' => $this->get('security.context')->getToken()->getUser(),
        );
    }
}
