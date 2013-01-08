<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentification\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\UserBundle\Form\UserType;
use Woda\UserBundle\Form\UserUpdateType;
use Woda\UserBundle\Entity\User;

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

    /**
     * @Route("/register", name="WodaUserBundle.Profile.register")
     * @Template("WodaUserBundle:Profile:register.html.twig")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);

        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);

                $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
                $user->setRoles(array('ROLE_USER'));

                $entityManager = $this->container->get('doctrine')->getEntityManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirect($this->generateUrl('WodaUserBundle.Security.login'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/profile/edit", name="WodaUserBundle.Profile.edit")
     * @Template("WodaUserBundle:Profile:edit.html.twig")
     */
    public function editAction(Request $request)
    {
      $user = $this->get('security.context')->getToken()->getUser();
      $form = $this->createForm(new UserUpdateType(), $user);

      if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $entityManager = $this->container->get('doctrine')->getEntityManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return ($this->redirect($this->generateUrl('WodaUserBundle.Profile.index')));
            }
        }

      return (
        array (
            'form' => $form->createView(),
        )
      );
    }

}
