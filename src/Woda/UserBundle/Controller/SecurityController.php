<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentification\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\UserBundle\Form\SecurityForgotPasswordType;
use Woda\UserBundle\Form\SecurityRecoveryPasswordType;
use Woda\UserBundle\Entity\UserRecoveryPassword;

class SecurityController extends Controller
{
    /**
     * @Route("/", name="WodaUserBundle.Security.login")
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        
        if ($this->get('security.context')->getToken()->getUser() != 'anon.')
            return ($this->redirect($this->generateUrl('WodaFSBundle.Default.list', array('path' => ''))));

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        
        return $this->render('WodaUserBundle:Security:login.html.twig', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error ? $error->getMessage() : null,
        ));
    }
    
    /**
     * @Route("/logout", name="WodaUserBundle.Security.logout")
     */
    public function logoutAction()
    {
    }
    
    /**
     * @Route("/login/check", name="WodaUserBundle.Security.check")
     */
    public function checkAction()
    {
    }

    
    /**
     * @Route("/forgot/password", name="WodaUserBundle.Security.forgotpassword")
     * @Template("WodaUserBundle:Security:forgotpassword.html.twig")
     */
    public function forgotPasswordAction()
    {
        $form = $this->createForm(new SecurityForgotPasswordType());

        $request = $this->getRequest();
        if ($request->getMethod() === 'POST') {
              $form->bindRequest($request);
              if ($form->isValid()) {
                  $data = $form->getData();
                  $email = $data['email'];
                  $em = $this->getDoctrine()->getManager();
                  $user = $em->getRepository('WodaUserBundle:User')->findOneByEmail($email);
                  if (is_null($user)) {
                      throw $this->createNotFoundException('Aucun compte activé lié avec cette adresse email n\'a été trouvé !');
                  }

                  $userRecoveryPassword = new UserRecoveryPassword();
                  $userRecoveryPassword->setUser($user);

                  $em->persist($userRecoveryPassword);
                  $em->flush();

                  $this->sendMail('WodaUserBundle:Security/Mail:recoveryPassword.html.twig', 'Woda -- Mot de passe oublie', array(
                        'login64' => base64_encode($user->getLogin()),
                        'token' => $userRecoveryPassword->getToken()
                    ), $user->getEmail()
                  );

                  return ($this->render('WodaUserBundle:Security/Message:default.html.twig', array(
                        'message' => 'Un email de redefinition de mot de passe a été envoyé à l\'adresse suivante: ' . $email . '.'
                    )
                  ));
              }
          }

        return (
          array (
              'form' => $form->createView(),
          )
        );
    }

    /**
     * @Route("/recovery/password/{login64}--{token}", name="WodaUserBundle.Security.recoverypassword", requirements={"login64"="[a-zA-Z0-9\/\+\=]*", "token"="[a-zA-Z0-9\.]*"})
     * @Template("WodaUserBundle:Security:recoverypassword.html.twig")
     */
    public function recoveryPasswordAction($login64, $token)
    {
        $login = base64_decode($login64);
        $em = $this->getDoctrine()->getManager();

        if (preg_match("/^[A-Za-z0-9-_]+$/", $login) !== 1) {
            throw $this->createNotFoundException('Aucun compte trouvé !');
        }

        $user = $em->getRepository('WodaUserBundle:User')->findOneByLogin($login);
        if (!$user) {
            throw $this->createNotFoundException('Aucun compte trouvé !');
        }

        $userRecoveryPasswords = $em->getRepository('WodaUserBundle:UserRecoveryPassword')->findBy(array('user' => $user), array('date' => 'desc'), array(0, 1));

        if (isset($userRecoveryPasswords[0]) &&
            ($userRecoveryPasswords[0]->getToken() === $token) &&
            ($userRecoveryPasswords[0]->getAvailable() == true)) {
            $form = $this->createForm(new SecurityRecoveryPasswordType());

            $request = $this->getRequest();
            if ($request->getMethod() === 'POST') {
                  $form->bindRequest($request);
                  if ($form->isValid()) {
                      $data = $form->getData();
                      $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                      $user->setPassword($encoder->encodePassword($data['password'], $user->getSalt()));
                      $userRecoveryPasswords[0]->setAvailable(false);

                      $em->persist($user);
                      $em->persist($userRecoveryPasswords[0]);
                      $em->flush();

                      return ($this->redirect($this->generateUrl('WodaUserBundle.Security.login')));
                  }
            }

            return (array(
                'form' => $form->createView(),
                'login64' => $login64,
                'token' => $token
                )
            );
        }

        return ($this->redirect($this->generateUrl('WodaContentBundle.Content.index')));
    }

    /**
     * @Route("/forgot/account", name="WodaUserBundle.Security.forgotaccount")
     * @Template("WodaUserBundle:Security:forgotpassword.html.twig")
     */
    public function forgotAccountAction()
    {
        // non implementé
        return ;
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
