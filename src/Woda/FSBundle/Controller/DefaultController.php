<?php

namespace Woda\FSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Woda\FSBundle\Entity\Folder;
use Woda\FSBundle\Entity\XFile;
use Woda\FSBundle\Entity\Content;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\ResultSetMapping;

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
     * @Route("/", defaults={"path" = ""}, name="WodaFSBundle.Default.list")
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

        return (array('active' => 'home', 'folders' => $folder->getFolders(), 'files' => $folder->getFiles(), 'path' => $path, 'paths'=>$this->getAllPaths()));
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

    private function randomKey() {
        $string = "";
        $chaine = "abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
        srand((double)microtime()*1000000);
        for($i=0; $i<32; $i++) {
            $string .= $chaine[rand()%strlen($chaine)];
        }
        return $string;
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
                $content->setCryptKey($this->randomKey());
                $content->setSize($filesize);
                $content->setFileType($uploadedFile->getMimeType());
                $objectManager->persist($content);
                $i = 0;
                $handle = fopen($filepath, "r");
                while (($filepart = fread($handle, $filepartsize)) && ($i == 0 || $upstatus->isOK()))
                {
                    $upstatus = $s3->create_object('woda-files', $filehash .'/'. $i, array('body' => $filepart));
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
                 $return = $this->deleteFiles($bucket .'/'. $subFolder);
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
        }
        return new Response(json_encode($return));
    }

    /**
     * Dowload actions that download file by id
     *
     * @Route("-download/{id}/", requirements={"_method" = "GET"}, name="WodaFSBundle.Default.download")
     */
    public function downloadAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:XFile');
        if (is_numeric($id))
          $file = $repository->findOneBy(array('id' => $id, 'user' => $user));
        else
        {
          $file = $repository->findOneBy(array('uuid' => $id));
        }
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
            foreach ($fileparts as $fpart)
            {
                $object = $s3->get_object('woda-files', $fpart, array());
                echo $object->body;
            }
        }
        else
            echo 'file iz null';

        return new Response();
    }

    private function deleteFile($file)
    {
        $hash = $file->getContentHash();
        $this->getDoctrine()->getEntityManager()->remove($file);
        $this->getDoctrine()->getEntityManager()->flush();

        $repository = $this->getDoctrine()
                       ->getManager()
                       ->getRepository('WodaFSBundle:XFile');
        $otherfiles = $repository->findOneBy(array('content_hash' => $hash));
        if ($otherfiles == null)
        {
            $s3 = $this->container->get('aws_s3');
            $fileparts = $s3->get_object_list('woda-files', array('prefix' => $hash));
            foreach ($fileparts as $fpart)
            {
                $object = $s3->delete_object('woda-files', $fpart);
            }
            $repository = $this->getDoctrine()
                       ->getManager()
                       ->getRepository('WodaFSBundle:Content');
            $content = $repository->findOneBy(array('content_hash' => $hash));
            $this->getDoctrine()->getEntityManager()->remove($content);
            $this->getDoctrine()->getEntityManager()->flush();
        }
    }

    /**
     * Delete file from system
     *
     * @Route("delete/{id}/", requirements={"_method" = "GET"}, name="WodaFSBundle.Default.delete")
     */
    public function deleteAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:XFile');
        $file = $repository->findOneBy(array('id' => $id, 'user' => $user));
        $response = new Response();
        if ($file != null)
            $this->deleteFile($file);
        else
            echo 'file iz null';
        return $response;
    }

    /**
     * Delete folder from system
     *
     * @Route("deletef/{id}/", requirements={"_method" = "GET"}, name="WodaFSBundle.Default.deletefolder")
     */
    public function deleteFolderAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:Folder');
        $folder = $repository->findOneBy(array('id' => $id, 'user' => $user));
        $response = new Response();
        if ($folder != null)
        {
            $this->getDoctrine()->getEntityManager()->remove($folder);
            $this->getDoctrine()->getEntityManager()->flush();
            $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:XFile');
            $files = $repository->findBy(array('parent' => $id, 'user' => $user));
            foreach ($files as $file)
            {
                $this->deleteFile($file);
            }
        }
        else
            echo 'folder iz null';
        return $response;
    }

    private function gen_uuid() {
         $uuid = array(
          'time_low'  => 0,
          'time_mid'  => 0,
          'time_hi'  => 0,
          'clock_seq_hi' => 0,
          'clock_seq_low' => 0,
          'node'   => array()
         );

         $uuid['time_low'] = mt_rand(0, 0xffff) + (mt_rand(0, 0xffff) << 16);
         $uuid['time_mid'] = mt_rand(0, 0xffff);
         $uuid['time_hi'] = (4 << 12) | (mt_rand(0, 0x1000));
         $uuid['clock_seq_hi'] = (1 << 7) | (mt_rand(0, 128));
         $uuid['clock_seq_low'] = mt_rand(0, 255);

         for ($i = 0; $i < 6; $i++) {
          $uuid['node'][$i] = mt_rand(0, 255);
         }

         $uuid = sprintf('%08x-%04x-%04x-%02x%02x-%02x%02x%02x%02x%02x%02x',
          $uuid['time_low'],
          $uuid['time_mid'],
          $uuid['time_hi'],
          $uuid['clock_seq_hi'],
          $uuid['clock_seq_low'],
          $uuid['node'][0],
          $uuid['node'][1],
          $uuid['node'][2],
          $uuid['node'][3],
          $uuid['node'][4],
          $uuid['node'][5]
         );

         return $uuid;
    }

    /**
     * Ajax call actions that adds a folder
     *
     * @Route("-file/{id}", name="WodaFSBundle.Default.publicdl")
     * @Template("WodaFSBundle:Default:download.html.twig")
     */
    public function publicDownloadAction($id)
    {
      $em = $this->getDoctrine()
                 ->getManager();

      $user = $this->get('security.context')->getToken()->getUser();

      // var_dump($user);

      $file = $em->getRepository('WodaFSBundle:XFile')->findOneBy(array('uuid' => $id));
      $user = $file->getUser();
      $content = $em->getRepository('WodaFSBundle:Content')->findOneBy(array('content_hash' => $file->getContentHash()));

      return array('id' => $id, 'file' => $file, 'user' => $user, 'content' => $content);
    }

    /**
     * Ajax call actions that adds a folder
     *
     * @Route("-getDLink/{id}", name="WodaFSBundle.Default.dLink")
     */
    public function getDownloadLink($id)
    {
        $response = null;
        if ($this->get('Request')->isXMLHttpRequest()) {
            $request = $this->get('request');
            $em = $this->getDoctrine()->getManager();
            $repository = $em->getRepository('WodaFSBundle:XFile');
            $user = $this->get('security.context')->getToken()->getUser();
            $xfile = $repository->findOneBy(array('id' => $id, 'user' => $user));
            if ($xfile == null)
            {
              echo 'file iz null';
              return new Response();
            }
            if ($xfile->getUuid() == null)
            {
              $uuid = $this->gen_uuid();
              $xfile->setUuid($uuid);
              $em->persist($xfile);
              $em->flush();
            }
            else
                $uuid = $xfile->getUuid();

            $response = array('id' => $uuid);
       }
       return new Response(json_encode($response));
    }

    /**
     * @Route("-movefile/", name="WodaFSBundle.Default.moveFile")
     */
    public function moveFileAction()
    {
        $request = $this->get('request');
        $id = $request->query->get('id');
        $target = substr($request->query->get('path'), 1);

        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()->getRepository('WodaFSBundle:Folder');
        $folder = $repository->findByPath($target, $user);
        if ($folder === null)
            return $this->redirect($this->generateUrl('WodaFSBundle.Default.list'));

        $file = $this->getDoctrine()->getRepository('WodaFSBundle:XFile')->find($id);

        $oldparent = $file->getParent();
        $oldparent->removeFile($file);
        $folder->addFile($file);
        $file->setParent($folder);

        $this->getDoctrine()->getEntityManager()->persist($oldparent);
        $this->getDoctrine()->getEntityManager()->persist($folder);
        $this->getDoctrine()->getEntityManager()->persist($file);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirect($this->generateUrl('WodaFSBundle.Default.list'));
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
        $user = $this->get('security.context')->getToken()->getUser();
        $files = $this->getDoctrine()->getRepository('WodaFSBundle:XFile')->findBy(array('user' => $user), array('lastModificationTime' => 'DESC'));

        return (array('active' => 'recent', 'folders' => array(), 'files' => $files, 'path' => null, 'paths'=>$this->getAllPaths()));
    }

    /**
     * @Route("-starit/{id}", name="WodaFSBundle.Default.star")
     */
    public function starAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:XFile');
        $file = $repository->findOneBy(array('id' => $id, 'user' => $user));

        if ($user->getFavorites()->contains($file)) {
            $user->removeFavorite($file);
        } else {
            $user->addFavorite($file);
        }

        $this->getDoctrine()->getEntityManager()->persist($user);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirect($this->generateUrl('WodaFSBundle.Default.starred'));
    }

    /**
     * @Route("-public/{id}", name="WodaFSBundle.Default.public")
     */
    public function publicAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:XFile');
        $file = $repository->findOneBy(array('id' => $id, 'user' => $user));

        $file->setPublic(!$file->isPublic());

        $this->getDoctrine()->getEntityManager()->persist($file);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirect($this->generateUrl('WodaFSBundle.Default.list'));
    }

    /**
     * @Route("-public-folder/{id}", name="WodaFSBundle.Default.publicFolder")
     */
    public function publicFolderAction($id)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repository = $this->getDoctrine()
                           ->getManager()
                           ->getRepository('WodaFSBundle:Folder');
        $folder = $repository->findOneBy(array('id' => $id, 'user' => $user));

        $folder->setPublic(!$folder->isPublic());

        $this->getDoctrine()->getEntityManager()->persist($folder);
        $this->getDoctrine()->getEntityManager()->flush();

        return $this->redirect($this->generateUrl('WodaFSBundle.Default.list'));
    }

    /**
     * @Route("-starred/", name="WodaFSBundle.Default.starred")
     * @Template("WodaFSBundle:Default:starred.html.twig")
     */
    public function starredAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        return (array('active' => 'starred', 'folders' => array(), 'files' => $user->getFavorites(), 'path' => null, 'paths'=>$this->getAllPaths()));
    }

    /**
     * @Route("-shared/", name="WodaFSBundle.Default.links")
     * @Template("WodaFSBundle:Default:links.html.twig")
     */
    public function sharedAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $files = $this->getDoctrine()->getRepository('WodaFSBundle:XFile')->findBy(array(
            'user' => $user,
            'public' => true
        ));

        return (array('active' => 'links', 'folders' => array(), 'files' => $files, 'path' => null, 'paths'=>$this->getAllPaths()));
    }

    /**
     * @Route("-sharing/", name="WodaFSBundle.Default.sharing")
     * @Template("WodaFSBundle:Default:sharing.html.twig")
     */
    public function sharingAction()
    {
        $id = $this->get('security.context')->getToken()->getUser()->getId();
        $query = 'SELECT user, friend, file FROM WodaUserBundle:User user JOIN user.friends friend JOIN friend.files file WHERE user.id = ' . $id . ' AND file.public = true';
        $files = $this->getDoctrine()->getEntityManager()->createQuery($query)->getResult();

        return (array('active' => 'sharing', 'folders' => array(), 'files' => $files, 'path' => null, 'paths'=>$this->getAllPaths()));
    }

    private function getAllPaths()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $repo = $this->getDoctrine()->getRepository('WodaFSBundle:Folder');

        $paths = array();
        foreach ($repo->findBy(array('user' => $user)) as $folder) {
            for ($parts = array(), $tmpfolder = $folder; $tmpfolder; $tmpfolder = $tmpfolder->getParent())
                $parts[] = $tmpfolder->getName();
            $path = implode('/', array_reverse($parts));
            $paths[] = empty($path) ? '/' : $path;
        }

        return $paths;
    }
}

?>
