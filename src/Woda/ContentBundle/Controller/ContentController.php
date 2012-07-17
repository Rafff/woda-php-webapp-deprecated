<?php

namespace Woda\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ContentController extends Controller
{
    /**
     * @Route("/", name="WodaContentBundle.Content.index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
