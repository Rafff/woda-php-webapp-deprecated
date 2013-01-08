<?php

namespace Woda\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woda\CmsBundle\Entity\Cms;
use Woda\CmsBundle\Form\CmsType;

/**
 * Cms controller.
 *
 * @Route("/admin/cms")
 */
class AdminController extends Controller
{
    /**
     * Lists all Cms entities.
     *
     * @Route("/list", name="woda_cms_admin_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WodaCmsBundle:Cms')->findAll();

        return (array('entities' => $entities));
    }

    /**
     * Displays a form to create a new Cms entity.
     *
     * @Route("/new", name="woda_cms_admin_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Cms();
        $form   = $this->createForm(new CmsType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Cms entity.
     *
     * @Route("/create", name="woda_cms_admin_create")
     * @Method("post")
     * @Template("WodaCmsBundle:Cms:new.html.twig")
     */
    public function createAction()
    {
        $entity  = new Cms();
        $request = $this->getRequest();
        $form    = $this->createForm(new CmsType(), $entity);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('woda_cms_admin_list'));
        } else {
            exit();
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Cms entity.
     *
     * @Route("/{id}/edit", name="woda_cms_admin_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WodaCmsBundle:Cms')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cms entity.');
        }

        $editForm = $this->createForm(new CmsType(), $entity);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Edits an existing Cms entity.
     *
     * @Route("/{id}/update", name="woda_cms_admin_update")
     * @Method("post")
     * @Template("WodaCmsBundle:Cms:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WodaCmsBundle:Cms')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cms entity.');
        }

        $editForm   = $this->createForm(new CmsType(), $entity);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('woda_cms_admin_list', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Deletes a Cms entity.
     *
     * @Route("/{id}/delete", name="woda_cms_admin_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WodaCmsBundle:Cms')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Cms entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('woda_cms_admin_list'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}


//@Template("WodaCmsBundle:Cms:test.html.twig")
/**
    *
    *
    * @Route("/{uri}", name="cms_test")
    */

/*
public function testAction($uri)
{
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('WodaCmsBundle:Cms')->findOneByUri($uri);

    if ($entity === null) {
        return $this->redirect($this->generateUrl('cms'));
    }

        return (array('entity' => $entity));
}

*/