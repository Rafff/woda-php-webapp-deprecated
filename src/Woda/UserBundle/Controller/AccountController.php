<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentification\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\UserBundle\Entity\User;
// use Woda\UserBundle\Form\UserType;
use Woda\UserBundle\Form\AccountEditInformationType;
use Woda\UserBundle\Form\AccountEditPasswordType;
use Woda\UserBundle\Form\AccountEditEmailType;

/**
 * Account controller.
 *
 * @Route("/account")
 */
class AccountController extends Controller
{
    /**
     * @Route("/", name="WodaUserBundle.Account.index")
     * @Template("WodaUserBundle:Account:index.html.twig")
     */
    public function indexAction()
    {
        return array(
            'user' => $this->get('security.context')->getToken()->getUser(),
        );
    }

    /**
     * @Route("/edit/information", name="WodaUserBundle.Account.editInformation")
     * @Template("WodaUserBundle:Account:editInformation.html.twig")
     */
    public function editAction(Request $request)
    {
      $user = $this->get('security.context')->getToken()->getUser();
      $form = $this->createForm(new AccountEditInformationType(), $user);

      if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $entityManager = $this->container->get('doctrine')->getEntityManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return ($this->redirect($this->generateUrl('WodaUserBundle.Account.index')));
            }
        }

      return (
        array (
            'form' => $form->createView(),
        )
      );
    }

    /**
     * @Route("/edit/email", name="WodaUserBundle.Account.editEmail")
     * @Template("WodaUserBundle:Account:editEmail.html.twig")
     */
    public function editEmailAction()
    {
        $form = $this->createForm(new AccountEditEmailType());
        $request = $this->getRequest();
        if ($request->getMethod() === 'POST') {
              $form->bindRequest($request);
              if ($form->isValid()) {
                  $data = $form->getData();
                  $em = $this->getDoctrine()->getManager();
                  $user = $this->get('security.context')->getToken()->getUser();

                  $user->setEmail($data['email']);

                  // envoyer un email

                  $em->persist($user);
                  $em->flush();

                  return ($this->redirect($this->generateUrl('WodaUserBundle.Account.index')));
              }
        }

        return (array('form' => $form->createView()));
    }

    /**
     * @Route("/edit/password", name="WodaUserBundle.Account.editPassword")
     * @Template("WodaUserBundle:Account:editPassword.html.twig")
     */
    public function editPasswordAction()
    {
        $form = $this->createForm(new AccountEditPasswordType());
        $request = $this->getRequest();
        if ($request->getMethod() === 'POST') {
              $form->bindRequest($request);
              if ($form->isValid()) {
                  $user = $this->get('security.context')->getToken()->getUser();
                  $data = $form->getData();
                  $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);

                  if ($encoder->encodePassword($data['actual_password'], $user->getSalt()) == $user->getPassword()) {
                    $em = $this->getDoctrine()->getManager();
                    $user->setPassword($encoder->encodePassword($data['password'], $user->getSalt()));

                    // envoyer un email
                    $em->persist($user);
                    $em->flush();
                  } else {
                      throw new \Exception('TODO'); // enlever l'exception et la remplacé par une erreur de formulaire.
                  }

                  return ($this->redirect($this->generateUrl('WodaUserBundle.Account.index')));
              }
        }

        return (array('form' => $form->createView()));
    }
}
