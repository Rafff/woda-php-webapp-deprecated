<?php

namespace Woda\ContentBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Woda\ContentBundle\Entity\Content;

class NormalController extends Controller
{
    /**
     * @Route("/", name="WodaContentBundle.Normal.index")
     * @Template("WodaContentBundle:Normal:index.html.twig")
     */
    public function indexAction()
    {
        return array(
        );
    }

    /**
     * @Route("/{id}-{slug}", name="WodaContentBundle.Normal.show", requirements={"id"="[0-9]+", "slug"="[^/]+"})
     * @ParamConverter("id", class="WodaContentBundle:Content")
     * @Template("WodaContentBundle:Normal:content.html.twig")
     */
    public function showAction(Content $content)
    {
        return array(
            'content' => $content,
        );
    }
}
