<?php

namespace Woda\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Search controller.
 *
 * @Route("/search", name="WodaSearchBundle.Search")
 */
class SearchController extends Controller
{
    /**
     * @Route("/test", name="WodaSearchBundle.Search.test")
     */
    public function testAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $r = $this->get('doctrine')->getRepository('WodaFSBundle:XFile')->search($user, 'su', array(), array(0, 50), 'file');

        if (!empty($r->result)) {
            foreach ($r->result as $res) {
                echo $res->getName();
            }
        }
        exit(0);
    }

    /**
     * @Route("/result/{type}/{length}/{offset}", name="WodaSearchBundle.Search.result", defaults={ "length" : 50, "offset" : 0, "type" : "file" }, requirements={ "type" : "private_file|shared|file|folder|movie|music|picture|user"})
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
                        'file_private',
                        'shared',
                        'file',
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
                        'link' => $res->getLogin()
                    );
                }
            }
        } else if ($type === 'folder') {
            //non implemente
        } else {
            $r = $this->get('doctrine')->getRepository('WodaFSBundle:XFile')->search($user, $query, $order, $limit, $type);

            $result->count = $r->count;
            if (!empty($r->result)) {
                foreach ($r->result as $res) {
                    $result->data[] = array(
                        'link' => $res->getName()
                    );
                }
            }
        }

        return ($result);
    }
}

/*
{% for t in types %}
            <div class="tab-pane{% if (type == t) %} active {% endif %}" id="{{ t }}s">
                {% if (type == t) %}
                    {% for d in data %}
                        <div><a href="#">{{ d.link }}</a></div>
                    {% endfor %}
                {% endif %}
            </div>
        {% endfor %}
 */

/*
    /**
     * @Route("/search", name="WodaSearchBundle.Search.result")
     * @Template("WodaSearchBundle:Search:result.html.twig")
     * /
    public function searchAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $result = new \stdClass();
        $result->data = array();
        $query = trim($request->get('query'));

        $fileRows = $this->get('doctrine')->getRepository('WodaFSBundle:XFile')
            ->search($user, $query, array(), array(0, 50));

        $files = array( );
        foreach ($fileRows as $row) {
            $files[] = array(
                'name' => $row->getName()
            );
        }

        $userRows = $this->get('doctrine')->getRepository('WodaUserBundle:User')
            ->findUserLikeLogin($query, array(), array(0, 50));

        $users = array();
        foreach ($userRows as $row) {
            $users[] = array(
                'name' => $row->getLogin()
            );
        }

        if ($request->isXmlHttpRequest()) {
            return (new Response(json_encode($result), 200, array('Content-Type'=>'application/json')));
        } else {
            return (
                array(
                    'displayed' => (!count($files) && count($users)) ? 'users' : 'files',
                    'query' => $query,
                    'files' => $files,
                    'users' => $users,
                )
            );
        }
    }
*/