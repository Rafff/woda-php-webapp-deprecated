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
use Woda\FSBundle\Entity\Content;
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

    //////////////////////////////////////////////////////////////////////////////


    private function getFileInfo($bucket, $fileName) {
        $s3 = $this->container->get('aws_s3');
        $fileArray = "";
        $size = $s3->get_object_filesize($bucket, $fileName);
        $furl = "http://" . $bucket . ".s3.amazonaws.com/" . $fileName;
        $fileArray['name'] = $fileName;
        $fileArray['size'] = $size;
        $fileArray['url'] = $furl;
        $fileArray['thumbnail'] = $furl;
        $fileArray['delete_url'] = "server/php/index.php?file=" . $fileName;
        $fileArray['delete_type'] = "DELETE";
        return $fileArray;
    }

    private function getListOfContents($bucket, $prefix="") {
        $s3 = $this->container->get('aws_s3');
        if ($prefix=="") {
          $contents = $s3->get_object_list($bucket);
        } else {
            $contents = $s3->get_object_list($bucket, array("prefix" => $prefix));
        }
        $resultArray = "";
        for ($i = 0;$i < count($contents);$i++) {
            $resultArray[] = getFileInfo($bucket, $contents[$i]);
        }
        return $resultArray;
    }


    private function uploadSingleFile($bucket, $uploadedFile, $user, $folder, $repository, $s3)
    {
        if (null === $uploadedFile)
                return false;
        if ($uploadedFile->isValid())
        {
            $filepath = $uploadedFile->getPathname();
            $filehash = hash_file('sha256', $filepath);
            $filesize = filesize($filepath);
            $filepartsize = 5 * 1024 * 1024;
            $objectManager = $this->getDoctrine()->getManager();

            $file = new XFile();
            $file->setParent($folder);
            $file->setUser($user);
            $file->setName($uploadedFile->getClientOriginalName());
            $file->setContentHash($filehash);
            $file->setFileType($uploadedFile->getMimeType());
            $time = new \Datetime();
            $file->setLastModificationTime($time);
            $objectManager->persist($file);

            $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:Content');
            $contentexists = $repository->findOneBy(array('content_hash' => $filehash));

            $upstatus = null;
            if ($contentexists == null)
            {
                $content = new Content();
                $content->setContentHash($filehash);
                $content->setCryptKey(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 32));
                $content->setSize($filesize);
                $content->setFileType($uploadedFile->getMimeType());
                $objectManager->persist($content);

                $filecontent = file_get_contents($filepath);
                $i = 0;
                $handle = fopen($filepath, "r");
                while (($filepart = fread($handle, $filepartsize)) && ($i == 0 || $upstatus->isOK()))
                {
                    $upstatus = $s3->create_object('woda-files', $filehash .'/'. $i, array('body' => $filepart, 'encryption'=>'AES256'));
                    $i++;
                }
                fclose($handle);
            }

            $response = array();
            $response['name'] = $uploadedFile->getClientOriginalName();
            if ($contentexists || ($upstatus != null &&$upstatus->isOK()))
            {
                $objectManager->flush();
                $response['time'] = $time->format('d/m/Y H:i');
                $response['id'] = $file->getId();
            }
            else
                $response['error'] = 's3 upload error';// upload error
        } else
            $response['error'] = 'file submit error';// ERROR HERE
        return $response;
    }

    private function uploadFiles($bucket, $prefix="") {
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return array('error' => 'DELETE isnt allowed');
        }
        $info = array();
        $request = $this->getRequest();
        $user = $this->get('security.context')->getToken()->getUser();
        $path = $request->request->get('path');
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:Folder');
        $folder = $repository->findByPath($path, $user);
        if ($folder === null)
            return array('error' => 'Folder does not exists');
        $s3 = $this->container->get('aws_s3');
        $upload = $request->files->get('files');

        if ($upload && is_array($upload)) {
            foreach($upload as $index => $value) {

                $info[] = $this->uploadSingleFile($bucket, $value, $user, $folder, $repository, $s3);
            }
        }
        else if ($upload)
            $info[] = $this->uploadSingleFile($bucket, $upload, $user, $folder, $repository, $s3);
        header('Vary: Accept');
        $json = json_encode($info);
        if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        $tmp = Array();
        $tmp['files'] = $info;
        return $tmp;
    }

    private function deleteFiles($bucket) {
        $s3 = $this->container->get('aws_s3');
        $file_name = isset($_REQUEST['file']) ? basename(stripslashes($_REQUEST['file'])) : null;
        $s3->delete_object($bucket, $file_name);
        $success = "";

        header('Content-type: application/json');
        return $success;
    }



/////////////////////////////////////////////////////////////////////////////



    /**
     * Ajax call actions that upload files
     *
     * @Route("fs-upload/", requirements={"_method" = "POST"}, name="WodaFSBundle.Default.upload")
     */
    public function uploadAction()
    {
        $request = $this->getRequest();

        $bucket = "woda-files";
        $subFolder = "";

        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Disposition: inline; filename="files.json"');
        header('X-Content-Type-Options: nosniff');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

        $return = "";
        switch ($request->server->get('REQUEST_METHOD')) {
            case 'OPTIONS':
                break;
            case 'HEAD':
            case 'GET':
                $return = $this->getListOfContents($bucket, $subFolder);
                break;
            case 'POST':
                if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
                    $return = $this->deleteObject($bucket, $subFolder);
                } else {
                    $return = $this->uploadFiles($bucket, $subFolder);
                }
                break;
            case 'DELETE':
                 $return = $this->deleteFiles($bucket, $subFolder);
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }
        return new Response(json_encode($return));
    }

    /**
     * Dowload actions that download file by id
     *
     * @Route("download/{id}/", requirements={"_method" = "GET"}, name="WodaFSBundle.Default.download")
     */
    public function downloadAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:XFile');
        $file = $repository->findOneBy(array('id' => $id, 'user' => $user));
        $response = new Response();
        if ($file != null)
        {
            $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:Content');
            $content = $repository->findOneBy(array('content_hash' => $file->getContentHash()));

            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', $content->getFileType());
            $response->headers->set('Content-Disposition', 'attachment;filename="' . $file->getName() . '"');
            $response->headers->set('Content-length', $content->getSize());
            $response->sendHeaders();
            
            $s3 = $this->container->get('aws_s3');
            $fileparts = $s3->get_object_list('woda-files', array('prefix' => $file->getContentHash()));
            $tmpfile = tmpfile();
            foreach ($fileparts as $fpart)
            {
                $object = $s3->get_object('woda-files', $fpart);//, array('fileDownload' => $tmpfile)
                echo $object->body;
            }

            // fseek($tmpfile, 0);

            // while ($r = fread($tmpfile, 5*1024*1024))
            // {
            //     echo $r;
            // }
            // fclose($tmpfile);
        }
        else
            echo 'file iz null';

        return new Response();
    }

    /**
     * Ajax call actions that adds a folder
     *
     * @Route("/addFolder/", requirements={"_method" = "POST"}, name="WodaFSBundle.Default.addFolder")
     */
    public function addFolderAction()
    {
        $isAjax = $this->get('Request')->isXMLHttpRequest();
        if ($isAjax) {
            $request = $this->get('request');
            $fname = $request->request->get('fname');
            $path = $request->request->get('path');

            if ($fname != "" && strpos($fname, '/') == false)
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
                $return = array("responseCode" => 403, "message" => "Filename Forbidden");
        }
        else
            $return = array("responseCode" => 403, "message"=>"Forbidden");

        $return = json_encode($return);
        $return = new Response($return);
        $return->headers->set('Content-Type', 'application/json');
        return $return;
    }

    /**
     * @Route("-recent/", name="WodaFSBundle.Default.recent")
     * @Template("WodaFSBundle:Default:recent.html.twig")
     */
    public function recentAction()
    {
        return (array());
    }

    /**
     * @Route("-starred/", name="WodaFSBundle.Default.starred")
     * @Template("WodaFSBundle:Default:starred.html.twig")
     */
    public function starredAction()
    {
        return (array());
    }

    /**
     * @Route("-shared/", name="WodaFSBundle.Default.shared")
     * @Template("WodaFSBundle:Default:shared.html.twig")
     */
    public function sharedAction()
    {
        return (array());
    }

    /**
     * @Route("-sharing/", name="WodaFSBundle.Default.sharing")
     * @Template("WodaFSBundle:Default:sharing.html.twig")
     */
    public function sharingAction()
    {
        return (array());
    }
}

?>