<?php

namespace Woda\FSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Woda\FSBundle\Entity\Folder;
use Woda\FSBundle\Entity\XFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * File System controller.
 *
 * @Route("/fs", name="WodaFSBundle.Default")
 */
class DefaultController extends Controller
{
    /**
     * Lists all FS files and folders.
     *
     * @Route("/", defaults={"path" = null}, name="WodaFSBundle.Default.list")
     * @Route("/{path}/", requirements={"path" = ".+", "_method" = "GET"}, name="WodaFSBundle.Default.list.param")
     * @Template()
     */
    public function listAction($path)
    {   
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:Folder');

        $folder = $repository->findByPath($path, $user);

        if ($folder === null)
            return ($this->redirect($this->generateUrl('WodaFSBundle.Default.list', array('path' => ''))));

        return (array('folders' => $folder->getFolders(), 'files' => $folder->getFiles(), 'path' => $path));
    }

    /**
     * Ajax call actions that adds a folder
     *
     * @Route("/addFolder/", requirements={"_method" = "POST"}, name="WodaFSBundle.Default.addFolder")
     */
    public function addFolderAction()
    {
        $isAjax = true;//$this->get('Request')->isXMLHttpRequest();
        if ($isAjax) {
            $request = $this->get('request');
            $fname = $request->request->get('fname');
            $path = $request->request->get('path');
           
            if ($fname != "")
            {
                $user = $this->get('security.context')->getToken()->getUser();
                if ($user != null)
                {
                    $repository = $this->getDoctrine()
                                       ->getManager()
                                       ->getRepository('WodaFSBundle:Folder');
                    $folder = $repository->findByPath($path, $user);
                    if ($folder === null)
                        $return = array("responseCode" => 403, "message"=>"Forbidden");

                    $folderExists = $repository->findOneBy(array('name' => $fname, 'parent' => $folder));
                    if ($folderExists === null)
                    {
                        $newfolder = new Folder();
                        $newfolder->setParent($folder);
                        $newfolder->setName($fname);
                        $newfolder->setLastModificationTime(new \Datetime());
                        $newfolder->setUser($user);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($newfolder);
                        $em->flush();

                        $return = array("responseCode" => 200,  "message" => "OK");
                    }
                    else
                        $return = array("responseCode" => 401, "message"=>"Folder exists");  
                }
                else
                    $return = array("responseCode" => 403, "message"=>"Forbidden");
            }
            else
                $return = array("responseCode" => 400, "message"=>"You must enter a folder name.");   
        }
        else
            $return = array("responseCode" => 403, "message"=>"Forbidden");
        
        $return = json_encode($return);
        $return = new Response($return);
        $return->headers->set('Content-Type', 'application/json');
        return $return;
    }

}

?>