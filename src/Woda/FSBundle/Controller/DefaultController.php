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

        print_r($path);

        return (array('folders' => $folder->getFolders(), 'files' => $folder->getFiles(), 'path' => $path));
    }

       /**
     * Ajax call actions that upload files
     *
     * @Route("fs-upload/", requirements={"_method" = "POST"}, name="WodaFSBundle.Default.upload")
     */
    public function uploadAction()
    {
        $request = $this->getRequest();
        $user = $this->get('security.context')->getToken()->getUser();
        $path = $request->request->get('path');
        var_dump($path);
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:Folder');

        $folder = $repository->findByPath($path, $user);

        if ($folder === null)
            return new Response('<html><body>null folder</body></html>');


        $s3 = $this->container->get('aws_s3');
        $uploadedFile = $request->files->get('upfile');

        var_dump($request->files->keys());

        foreach ($request->files->keys() as $key)
        {
            echo 'YAY >> ';
            var_dump($request->files->get($key));
        }
        
        if (null === $uploadedFile)
            return new Response('<html><body>null up</body></html>');

        $filepath = $uploadedFile->getPathname();

        var_dump($uploadedFile->getMimeType());

        $filename = hash_file('sha256', $filepath);
        var_dump($filename);

        $file = new XFile();
        $file->setParent($folder);
        $file->setUser($user);
        $file->setName($uploadedFile->getClientOriginalName());
        $file->setFileHash($filename);
        $file->setFileType($uploadedFile->getMimeType());
        $file->setLastModificationTime(new \Datetime());
        $objectManager = $this->getDoctrine()->getManager();
        $objectManager->persist($file);

        //$upstatus = $s3->create_object('woda-files', $filename, array('fileUpload' => $filepath));

        if ($upstatus->isOK())
        {
            $objectManager->flush();
            echo 'YEP';
        }
        else
            echo 'NOPE';

        return false;
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