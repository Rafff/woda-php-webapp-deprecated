<?php

namespace Woda\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\SearchBundle\Form\SearchType;

/**
 * Search controller.
 *
 * @Route("/search", name="WodaSearchBundle.Search")
 */
class SearchController extends Controller
{
    /**
     * @Route("/result", name="WodaSearchBundle.Search.result")
     * @Template("WodaSearchBundle:Search:result.html.twig")
     */
    public function resultAction(Request $request)
    {
        $results = array();

        if ($request->getMethod() === 'POST') {
            $request->get('query');

            $results = $this->get('doctrine')->getRepository('WodaUserBundle:User')->findAllLikeLogin($request->get('query'));
        }

        return (array('results' => $results));
    }
}