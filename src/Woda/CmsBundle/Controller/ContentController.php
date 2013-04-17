<?php

namespace Woda\CmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContentController extends Controller
{
    /**
     * @Route("/", name="WodaCmsBundle.Content.index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/team", name="WodaCmsBundle.Content.team")
     * @Template()
     */
    public function teamAction()
    {
        return array();
    }

    /**
     * @Route("/contact", name="WodaCmsBundle.Content.contact")
     * @Template()
     */
    public function contactAction()
    {
        return array();
    }
}
