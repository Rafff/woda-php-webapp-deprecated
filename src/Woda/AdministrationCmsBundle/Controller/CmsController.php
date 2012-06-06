<?php

namespace Woda\AdministrationCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CmsController extends Controller
{
    /**
     * @Route("/")
     * @Route("/index")
     */
    public
    function indexAction()
    {
        // @Template("WodaAdministrationCmsBundle:Index:index.html.twig")
        return $this->render('WodaAdministrationCmsBundle:Cms:index.html.twig', array('foo' => 'foo'));
    }

    /**
     * @Route("/list")
     * @Template("WodaAdministrationCmsBundle:Cms:list.html.twig")
     */
    public
    function listAction()
    {
        $cmsCollection = new \Woda\AdministrationCmsBundle\CmsCollection();

        $cms = new \Woda\AdministrationCmsBundle\Entity\Cms();
        $cms->setContent('toto');
        $cmsCollection->push($cms);

        $cms = new \Woda\AdministrationCmsBundle\Entity\Cms();
        $cms->setContent('tata');
        $cmsCollection->push($cms);

        $cms = new \Woda\AdministrationCmsBundle\Entity\Cms();
        $cms->setContent('titi');
        $cmsCollection->push($cms);


        //echo '<pre>';
        //var_dump($cmsCollection);

        return (array('list_cms' => $cmsCollection->get()));
    }

    /**
     * @Route("/view/{id}")
     */
    public
    function viewAction($id)
    {
        echo $id . '<br />';
    }
}