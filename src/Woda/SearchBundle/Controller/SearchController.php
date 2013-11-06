<?php

namespace Woda\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woda\Bundle\FSBundle\Entity\FolderRepository as FolderRepository;

/**
 * Search controller.
 *
 * @Route("/search", name="WodaSearchBundle.Search")
 */
class SearchController extends Controller
{
    /**
     * @Route("/result/{type}/{length}/{offset}", name="WodaSearchBundle.Search.result", defaults={ "length" : 50, "offset" : 0, "type" : "no_media" }, requirements={ "type" : "private_file|shared|no_media|folder|movie|music|picture|user"})
     * @Template("WodaSearchBundle:Search:result.html.twig")
     */
    public function resultAction($type, $length, $offset)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest() && !($request->getMethod() === 'POST')) {
            throw new \Exception('TODO');
        }

        $result = new \stdClass();
        $result->data = array();
        $query = trim($request->get('query'));

        $r = $this->getResult($type, $user, $query, array(), array($offset, $length));

        $result->data = $r->data;
        $result->count = $r->count;
        $result->length = intval($length);
        $result->offset = intval($offset) + intval($length);

        if ($request->isXmlHttpRequest()) {
            return (new Response(json_encode($result), 200, array('Content-Type'=>'application/json')));
        } else {
            return (
                array(
                    'query' => $query,
                    'type' => $type,
                    'types' => array(
                        'private_file',
                        'no_media',
                        'folder',
                        'movie',
                        'music',
                        'picture',
                        'user'
                        ),
                    'length' => $length,
                    'offset' => $offset,
                    //'data' => $result->data,
                    'count' => $result->count
                )
            );
        }
    }

    private function getResult($type, $user, $query, $order, $limit)
    {
        $result = new \stdClass();
        $result->data = array();
        $result->count = 0;

        if ($type === 'user') {
            $r = $this->get('doctrine')->getRepository('WodaUserBundle:User')->search($query, $order, $limit);
            
            $result->count = $r->count;
            if (!empty($r->result)) {
                foreach ($r->result as $res) {
                    $result->data[] = array(
                        'id' => $res->getId(),
                        'link' => $res->getLogin()
                    );
                }
            }
        } else if ($type === 'folder') {
            $r = $this->get('doctrine')->getRepository('WodaFSBundle:Folder')->search($user, $query, $order, $limit);

            $result->count = $r->count;
            if (!empty($r->result)) {
                foreach ($r->result as $res) {
                    $result->data[] = array(
                        'id' => $res->getId(),
                        'link' => $res->getName(),
                        'link_encoded' => rawurlencode($res->getName()),
                        'owner' => $res->getUser()->getLogin(),
                        'date' => $res->getLastModificationTime()->format('d/m/Y H:i')
                    );
                }
            }
        } else {
            $r = $this->get('doctrine')->getRepository('WodaFSBundle:XFile')->search($user, $query, $order, $limit, $type);

            $result->count = $r->count;
            if (!empty($r->result)) {
                foreach ($r->result as $res) {
                    $result->data[] = array(
                        'id' => $res->getId(),
                        'link' => $res->getName(),
                        'link_encoded' => rawurlencode($res->getName()),
                        'owner' => $res->getUser()->getLogin(),
                        'date' => $res->getLastModificationTime()->format('d/m/Y H:i')
                    );
                }
            }
        }

        return ($result);
    }
}