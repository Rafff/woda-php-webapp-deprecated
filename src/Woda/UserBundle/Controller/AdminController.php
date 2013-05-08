<?php

namespace Woda\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\UserBundle\Entity\User;
use Woda\UserBundle\Form\AdminUserType as UserType; // peut etre enlever le as xxx

/**
 * @Route("/admin/users")
 */
class AdminController extends Controller
{
    /**
     * @Route("/list", name="WodaUserBundle.Admin.list")
     * @Template()
     */
    public function listAction()
    {
        $users = $this->get('doctrine')->getRepository('WodaUserBundle:User')->findAll();

        return array(
            'users' => $users,
        );
    }

    /**
     * @Route("/edit/{id}", name="WodaUserBundle.Admin.edit")
     * @Template()
     */
    public function editAction(Request $req, User $user)
    {
        $editionForm = $this->createForm(new UserType($user), $user);
        if ($req->getMethod() === 'POST') {
            $editionForm->bind($req);
            if ($editionForm->isValid()) {
                die($user->getPassword());
                return $this->redirect($this->generateUrl('WodaUserBundle.Admin.list'));
            }
        }

        return array(
            'user' => $user,
            'editionForm' => $editionForm->createView(),
        );
    }

    /**
     * @Route("/remove/{id}", name="WodaUserBundle.Admin.remove")
     * @Method({"GET"})
     * @Template()
     */
    public function removeAction(Request $req, User $user)
    {
        $confirmation = new \StdClass;
        $confirmation->confirm = false;

        $confirmationForm = $this->createFormBuilder($confirmation)
            ->add('confirm', 'hidden', array('data' => '1'))->getForm();

        return array(
            'user' => $user,
            'confirmationForm' => $confirmationForm->createView(),
        );
    }

    /**
     * @Route("/remove/{id}", name="WodaUserBundle.Admin.confirmRemoval")
     * @Method({"POST"})
     */
    public function confirmRemovalAction(Request $req, User $user)
    {
        $confirmation = new \StdClass;
        $confirmation->confirm = false;

        $confirmationForm = $this->createFormBuilder($confirmation)
            ->add('confirm', 'hidden', array('data' => '1'))->getForm();

        $confirmationForm->bind($req);
        if ($confirmationForm->isValid()) {
            $this->getDoctrine()->getEntityManager()->remove($user);
            $this->getDoctrine()->getEntityManager()->flush();
            return $this->redirect($this->generateUrl('WodaUserBundle.Admin.list'));
        } else {
            return $this->redirect($this->generateUrl('WodaUserBundle.Admin.remove', array('id' => $user->getId())));
        }
    }
}
