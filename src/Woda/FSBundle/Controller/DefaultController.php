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
            $filename = hash_file('sha256', $filepath);

            $file = new XFile();
            $file->setParent($folder);
            $file->setUser($user);
            $file->setName($uploadedFile->getClientOriginalName());
            $file->setFileHash($filename);
            $file->setFileType($uploadedFile->getMimeType());
            $time = new \Datetime();
            $file->setLastModificationTime($time);
            $objectManager = $this->getDoctrine()->getManager();
            $objectManager->persist($file);

            $upstatus = $s3->create_object('woda-files', $filename, array('fileUpload' => $filepath));

            if ($upstatus->isOK())
            {
                $objectManager->flush();
                $response = array();
                $response['name'] = $uploadedFile->getClientOriginalName();
                $response['time'] = $time->format('d/m/Y H:i');
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
        //$path = $request->request->get('path');
        $path = null;
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
        $subFolder = "";  // leave blank for upload into the bucket directly

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


    // /**
    //  * Ajax call actions that upload files
    //  *
    //  * @Route("fs-upload/", requirements={"_method" = "POST"}, name="WodaFSBundle.Default.upload")
    //  */
    // public function uploadAction()
    // {
    //     $request = $this->getRequest();
    //     $user = $this->get('security.context')->getToken()->getUser();
    //     $path = $request->request->get('path');
    //     $repository = $this->getDoctrine()
    //                        ->getManager()
    //                        ->getRepository('WodaFSBundle:Folder');

    //     $folder = $repository->findByPath(null, $user);

    //     if ($folder === null)
    //         return false;
    //     else
    //         echo 'YUP';

    //     $s3 = $this->container->get('aws_s3');
    //     $uploadedFile = $request->files->get('files');

    //     var_dump($uploadedFile);

    //     if (null === $uploadedFile)
    //         return false;
    //     else
    //         echo 'nope';

    //     $filepath = $uploadedFile->getPathname();
    //     $filename = hash_file('sha256', $filepath);

    //     $file = new XFile();
    //     $file->setParent($folder);
    //     $file->setUser($user);
    //     $file->setName($uploadedFile->getClientOriginalName());
    //     $file->setFileHash($filename);
    //     $file->setFileType($uploadedFile->getMimeType());
    //     $time = new \Datetime();
    //     $file->setLastModificationTime($time);
    //     $objectManager = $this->getDoctrine()->getManager();
    //     $objectManager->persist($file);

    //     //$upstatus = $s3->create_object('woda-files', $filename, array('fileUpload' => $filepath));

    //     if ($upstatus->isOK())
    //     {
    //         $objectManager->flush();
    //         $response = array();
    //         $response['name'] = $uploadedFile->getClientOriginalName();
    //         $response['time'] = $time->format('d/m/Y H:i');
    //         return new Response(json_encode($response));
    //     }
    //     else
    //         echo 'NOPE';

    //     return new Response(json_encode(false));
    // }

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