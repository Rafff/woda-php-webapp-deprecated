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
     * @Route("/test", name="WodaUserBundle.Account.test")
     * @Template("WodaUserBundle:Account:index.html.twig")
     */
    public function testAction(Request $request)
    {

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('service@woda-server.com')
            ->setTo('einsenhorn@gmail.com')
            ->setBody($this->renderView('WodaUserBundle:Account/Message:default.html.twig', array('message' => 'test')))
        ;

        $this->get('mailer')->send($message);

        return array(
            'user' => $this->get('security.context')->getToken()->getUser(),
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

                  $userEmail = new UserEmail();
                  $userEmail->setUser($user);
                  $userEmail->setEmail($data['new_email']);

                  $this->sendMail('WodaUserBundle:Account/Mail:confirmationEmail.html.twig', 'Woda -- Modification d\'adresse email', array(
                        'token' => $userEmail->getToken()
                    ), $user->getEmail()
                  );

                  $em->persist($userEmail);
                  $em->flush();

                  return ($this->render('WodaUserBundle:Account/Message:default.html.twig', array(
                        'message' => 'Un email de confirmation de redefinition de votre email a été envoyé à l\'adresse mail du compte.'
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

                $this->sendMail('WodaUserBundle:Account/Mail:confirmationPassword.html.twig', 'Woda -- Modification de mot de passe', array(
                      'token' => $userPassword->getToken()
                  ), $user->getEmail()
                );

                $em->persist($userPassword);
                $em->flush();

                return ($this->render('WodaUserBundle:Account/Message:default.html.twig', array(
                        'message' => 'Un email de confirmation de redefinition de mot de passe a été envoyé à l\'adresse mail du compte.'
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

    private function sendMail($template, $subject, $data, $to, $from = null)
    {
        $from = is_null($from) ? 'service@woda-server.com' : $from;

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setContentType('text/html')
            ->setBody($this->renderView($template, $data))
        ;

        $this->get('mailer')->send($message);
    }
}
