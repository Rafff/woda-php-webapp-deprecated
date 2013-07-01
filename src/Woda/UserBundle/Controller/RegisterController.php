<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentification\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\UserBundle\Form\UserType;
use Woda\UserBundle\Entity\User;
use Woda\UserBundle\Entity\UserValidation;

/**
 * Register controller.
 *
 * @Route("/register")
 */
class RegisterController extends Controller
{
    /**
     * @Route("/", name="WodaUserBundle.Register.index")
     * @Template("WodaUserBundle:Register:index.html.twig")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);

        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $userValidation = new UserValidation();
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $entityManager = $this->container->get('doctrine')->getEntityManager();

                $user->setPassword($encoder->encodePassword($user->getPassword(), $user->getSalt()));
                $user->setRoles(array('ROLE_USER'));
                $userValidation->setUser($user);

                $entityManager->persist($user);
                $entityManager->persist($userValidation);
                $entityManager->flush();

                $this->sendMail('WodaUserBundle:Register/Mail:register.html.twig', 'Inscription sur Woda', array(
                        'login64' => base64_encode($user->getLogin()),
                        'token' => $userValidation->getToken()
                    ), $user->getEmail()
                );

                return ($this->render('WodaUserBundle:Register/Message:default.html.twig', array(
                    'message' => 'Un email de confirmation a été envoyé à l\'adresse suivante: ' . $user->getEmail()
                )));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/confirmation/{login64}--{token}", name="WodaUserBundle.Register.confirmation", requirements={"login64"="[a-zA-Z0-9\/\+\=]*", "token"="[a-zA-Z0-9\.]*"})
     * @Template("WodaUserBundle:Register:confirmation.html.twig")
     */
    public function confirmationAction($login64, $token)
    {
        $login = base64_decode($login64);
        $em = $this->getDoctrine()->getManager();

        if (preg_match("/^[A-Za-z0-9-_]+$/", $login) !== 1) {
            throw $this->createNotFoundException('Aucun compte trouvé !');
        }

        $user = $em->getRepository('WodaUserBundle:User')->findOneByLogin($login);
        if (!$user) {
            throw $this->createNotFoundException('Aucun compte trouvé !');
        } else if ($user->isEnabled()) {
            throw new \Exception('Compte déjà activé ...');
        }

        $userValidation = $em->getRepository('WodaUserBundle:UserValidation')->findOneByUser($user);
        if (!is_null($userValidation) && $userValidation->getToken() === $token) {
            $user->setActive(true);
            $em->persist($user);
            $em->flush();
        } else {
            throw new \Exception('Aucune correspondance.');
        }

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