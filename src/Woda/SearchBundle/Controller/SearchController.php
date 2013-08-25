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
     * @Route("/search", name="WodaSearchBundle.Search.result")
     * @Template("WodaSearchBundle:Search:result.html.twig")
     */
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
}