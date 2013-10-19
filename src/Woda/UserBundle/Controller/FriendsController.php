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
     * @Route("/add", name="WodaUserBundle.Friends.add")
     */
    public function addAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $others = $this->getDoctrine()->getRepository('WodaUserBundle:User')->findBy(array('email' => $request->get('email')));

        if (count($others) === 0)
            return $this->redirect($this->generateUrl('WodaUserBundle.Friends.list', array('error' => 1)));

        $other = $others[0];
        if (!$user->getFriends()->contains($other))
            $user->addFriend($other);

        $this->getDoctrine()->getEntityManager()->persist($user);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirect($this->generateUrl('WodaUserBundle.Friends.list'));
    }

    /**
     * @Route("/remove", name="WodaUserBundle.Friends.remove")
     */
    public function removeAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $others = $this->getDoctrine()->getRepository('WodaUserBundle:User')->findBy(array('id' => $request->get('id')));

        if (count($others) === 0)
            return $this->redirect($this->generateUrl('WodaUserBundle.Friends.list', array('error' => 1)));

        $other = $others[0];
        if ($user->getFriends()->contains($other))
            $user->removeFriend($other);

        $this->getDoctrine()->getEntityManager()->persist($user);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirect($this->generateUrl('WodaUserBundle.Friends.list'));
    }

    /**
     * @Route("/", name="WodaUserBundle.Friends.list")
     * @Template("WodaUserBundle:Friends:list.html.twig")
     */
    public function listAction()
    {
        $error = array(1 => 'User not found')[$this->getRequest()->query->get('error')];
        $user = $this->get('security.context')->getToken()->getUser();
        return (array('friends' => $user->getFriends(), 'error' => $error));
    }
}
