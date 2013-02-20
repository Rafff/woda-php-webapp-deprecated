<?php

namespace Woda\FSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/{path}/", requirements={"path" = ".+"})
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

        return (array('folders' => $folder->getFolders(), 'files' => $folder->getFiles()));
    }
}

?>