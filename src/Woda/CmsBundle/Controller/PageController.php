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
 * @Route("/page")
 */
class PageController extends Controller
{
    /**
     *
     * @Route("/{uri}", name="woda_cms_page_show")
     * @Template("WodaCmsBundle:Page:show.html.twig")
     */
    public function showAction($uri)
    {
      $em = $this->getDoctrine()->getManager();

      $page = $em->getRepository('WodaCmsBundle:Cms')->findOneByUri($uri);

      if (!$page || !$page->getActivated()) {
          throw $this->createNotFoundException('This page doesn\'t exist.');
      }

      return array(
          'page' => $page
      );
    }
}