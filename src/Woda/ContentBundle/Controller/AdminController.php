<?php

namespace Woda\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/contents")
 */
class AdminController extends Controller
{
    /**
     * @Route("/list", name="WodaContentBundle.Admin.list")
     * @Template()
     */
    public function listAction()
    {
        $contents = $this->get('doctrine')->getRepository('WodaContentBundle:Content')->findAll();

        return array(
            'contents' => $contents,
        );
    }
}
