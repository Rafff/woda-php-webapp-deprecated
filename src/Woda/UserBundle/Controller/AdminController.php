<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/users")
 */
class AdminController extends Controller
{
    /**
     * @Route("/list")
     * @Template()
     */
    public function listAction()
    {
        $doctrine = $this->get('doctrine');
        $entityManager = $doctrine->getEntityManager();
        $repository = $entityManager->getRepository('WodaUserBundle:User');

        return array(
            'users' => $repository->findAll(),
        );
    }
}
