<?php

namespace Woda\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/result/{type}/{length}/{offset}", name="WodaSearchBundle.Search.result", defaults={ "length" : 50, "offset" : 0 }, requirements={ "type" : "file|user"})
     * @Template("WodaSearchBundle:Search:result.html.twig")
     */
    public function resultAction($type, $length, $offset)
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest() && !($request->getMethod() === 'POST')) {
            throw new \Exception('TODO');
        }

        $data = array();
        for ($idx = 0 ; $idx < 1000 ; ++$idx) {
            $data[]['name'] = $idx;
        }

        $result = new \stdClass();
        $query = trim($request->get('query'));

        // $this->get('doctrine')->getRepository('WodaUserBundle:User')->findAllLikeLogin($request->get('query'));

        $result->count = 3000;
        $result->length = intval($length);
        $result->offset = intval($offset) + intval($length);//$result->length;

        //$result->error = 'Une erreur est survenue';

        /**/
        $result->data = array_slice($data, $offset, $length);
        if ($type == 'file') {
            $result->count = 6;
            shuffle($result->data);
        }

        /**/
 
        if ($request->isXmlHttpRequest()) {
            return (new Response(json_encode($result), 200, array('Content-Type'=>'application/json')));
        } else {
            return (
                array(
                    'type' => $type,
                    'query' => $query,
                    'fileOffset' => ($type == 'file') ? ($offset) : (0),
                    'userOffset' => ($type == 'user') ? ($offset) : (0),
                    'length' => $length,
                    'data' => $result->data,
                    'count' => $result->count
                    
                )
            );
        }
    }
}