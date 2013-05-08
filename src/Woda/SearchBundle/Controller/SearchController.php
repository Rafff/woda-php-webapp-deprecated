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
     * @Route("/result/{type}/{length}/{offset}", name="WodaSearchBundle.Search.result", defaults={ "length" : 50, "offset" : 0, "type" : "file" }, requirements={ "type" : "file|user"})
     * @Template("WodaSearchBundle:Search:result.html.twig")
     */
    public function resultAction($type, $length, $offset)
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest() && !($request->getMethod() === 'POST')) {
            throw new \Exception('TODO');
        }

        $result = new \stdClass();
        $result->data = array();
        $query = trim($request->get('query'));

        if ($type == "file") {
            $results = $this->get('doctrine')->getRepository('WodaFSBundle:XFile')->findFileLikeName($query, array(), array($offset, $length));

            if (!empty($results)) {
                foreach ($results as $r) {
                    $result->data[] = array(
                        'name' => $r->getName()
                    );
                }
            }

        } else {
            $results = $this->get('doctrine')->getRepository('WodaUserBundle:User')->findUserLikeLogin($query, array(), array($offset, $length));

            if (!empty($results)) {
                foreach ($results as $r) {
                    $result->data[] = array(
                        'name' => $r->getLogin()
                    );
                }
            }
        }

        $result->count = ($type == 'file') ? 6 : 3000;
        $result->length = intval($length);
        $result->offset = intval($offset) + intval($length);

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