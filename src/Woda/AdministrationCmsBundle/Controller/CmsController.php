<?php

namespace Woda\AdministrationCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woda\AdministrationCmsBundle\Entity\Cms;
use Woda\AdministrationCmsBundle\Form\CmsType;

/**
 * Cms controller.
 *
 * @Route("/cms")
 */
class CmsController extends Controller
{
    /**
     * Lists all Cms entities.
     *
     * @Route("/", name="cms")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WodaAdministrationCmsBundle:Cms')->findAll();

        return (array('entities' => $entities));
    }

    /**
     * Finds and displays a Cms entity.
     *
     * @Route("/{id}/show", name="cms_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WodaAdministrationCmsBundle:Cms')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cms entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Cms entity.
     *
     * @Route("/new", name="cms_new")
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
     * @Route("/create", name="cms_create")
     * @Method("post")
     * @Template("WodaAdministrationCmsBundle:Cms:new.html.twig")
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

            return $this->redirect($this->generateUrl('cms_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Cms entity.
     *
     * @Route("/{id}/edit", name="cms_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WodaAdministrationCmsBundle:Cms')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cms entity.');
        }

        $editForm = $this->createForm(new CmsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Cms entity.
     *
     * @Route("/{id}/update", name="cms_update")
     * @Method("post")
     * @Template("WodaAdministrationCmsBundle:Cms:edit.html.twig")
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WodaAdministrationCmsBundle:Cms')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Cms entity.');
        }

        $editForm   = $this->createForm(new CmsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('cms_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Cms entity.
     *
     * @Route("/{id}/delete", name="cms_delete")
     * @Method("post")
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WodaAdministrationCmsBundle:Cms')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Cms entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('cms'));
    }

    /**
     *
     *
     * @Route("/{uri}", name="cms_test")
     * @Template("WodaAdministrationCmsBundle:Cms:test.html.twig")
     */
    public function testAction($uri)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WodaAdministrationCmsBundle:Cms')->findOneByUri($uri);

        if ($entity === null) {
            return $this->redirect($this->generateUrl('cms'));
        }

         return (array('entity'      => $entity));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
