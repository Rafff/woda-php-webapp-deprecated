<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentification\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormError;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\UserBundle\Entity\User;
use Woda\UserBundle\Entity\UserEmail;
use Woda\UserBundle\Entity\UserPassword;

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
      // <!--<li><a href="{{ path('WodaUserBundle.Account.editInformation') }}">Modifier les informations de mon profil</a></li>-->
      /*
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
      */
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

                  $userEmail = new UserEmail();
                  $userEmail->setUser($user);
                  $userEmail->setEmail($data['new_email']);

                  // envoyer un email a $user->getEmail();

                  $em->persist($userEmail);
                  $em->flush();

                  return ($this->render('WodaUserBundle:Account/Message:default.html.twig', array(
                        'message' => 'Un email de confirmation de redefinition de votre email a été envoyé à l\'adresse mail du compte.'.'['.$userEmail->getToken().']'
                    ))
                );
              }
        }

        return (array('form' => $form->createView()));
    }

    /**
     * @Route("/confirmation/email/{token}", name="WodaUserBundle.Account.confirmationEmail", requirements={"token"="[a-zA-Z0-9\.]*"})
     * @Template("WodaUserBundle:Account/Message:confirmation.html.twig")
     */
    public function confirmationEmailAction($token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $userEmails = $em->getRepository('WodaUserBundle:UserEmail')->findBy(array('user' => $user), array('date' => 'desc'), array(0, 1));
        if (!isset($userEmails[0])) {
            throw new \Exception('Aucune demande de modification d\'adresse mail n\'a été trouvé pour ce compte.');
        } else if (($userEmails[0]->getToken() !== $token) || ($userEmails[0]->getAvailable() == false)) {
            throw new \Exception('Le token ne correspond pas à celui du mail ou a déjà été utilisé.');
        }

        $user->setEmail($userEmails[0]->getEmail());

        $userEmails[0]->setAvailable(false);
        $em->persist($userEmails[0]);
        $em->persist($user);
        $em->flush();

        return (array(
            'message' => 'Votre adresse mail a été modifié !'
        ));
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

            $data = $form->getData();
            $user = $this->get('security.context')->getToken()->getUser();
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);

            if ($encoder->encodePassword($data['current_password'], $user->getSalt()) != $user->getPassword()) {
                $form->get('current_password')->addError(new FormError('Le mot de passe actuel n\'est pas correct.'));
            }

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $userPassword = new UserPassword();
                $userPassword->setUser($user);
                $userPassword->setPassword($encoder->encodePassword($data['password'], $user->getSalt()));

                // envoyer un email

                $em->persist($userPassword);
                $em->flush();

                return ($this->render('WodaUserBundle:Account/Message:default.html.twig', array(
                        'message' => 'Un email de confirmation de redefinition de mot de passe a été envoyé à l\'adresse mail du compte.'.'['.$userPassword->getToken().']'
                    ))
                );
            }
        }

        return (array('form' => $form->createView()));
    }

    /**
     * @Route("/confirmation/password/{token}", name="WodaUserBundle.Account.confirmationPassword", requirements={"token"="[a-zA-Z0-9\.]*"})
     * @Template("WodaUserBundle:Account/Message:confirmation.html.twig")
     */
    public function confirmationPasswordAction($token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        $userPasswords = $em->getRepository('WodaUserBundle:UserPassword')->findBy(array('user' => $user), array('date' => 'desc'), array(0, 1));
        if (!isset($userPasswords[0])) {
            throw new \Exception('Aucune demande de redéfinition de mot de passe n\'a été trouvé pour ce compte.');
        } else if (($userPasswords[0]->getToken() !== $token) || ($userPasswords[0]->getAvailable() == false)) {
            throw new \Exception('Le token ne correspond pas à celui du mail ou a déjà été utilisé.');
        }

        $user->setPassword($userPasswords[0]->getPassword());

        $userPasswords[0]->setAvailable(false);
        $em->persist($userPasswords[0]);
        $em->persist($user);
        $em->flush();

        return (array(
            'message' => 'Votre mot de passe a été modifié !'
        ));
    }
}
