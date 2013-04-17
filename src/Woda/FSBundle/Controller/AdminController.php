<?php

namespace Woda\FSBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Woda\FSBundle\Entity\Folder;
use Woda\FSBundle\Entity\XFile;
use Woda\UserBundle\Entity\User;

class AdminController extends Controller
{
	/**
	 * @Route("/admin/trees/", name="WodaFSBundle.Admin.list")
	 * @Template()
	 */
	public function listAction() {
		$users = $this->getDoctrine()->getRepository('WodaUserBundle:User')->findAll();
		return array('users' => $users);
	}

	/**
	 * @Route("/admin/users/{user}/tree", name="WodaFSBundle.Admin.tree")
	 * @Template("WodaFSBundle:Admin:fs.html.twig")
	 * @ParamConverter("user", class="WodaUserBundle:User")
	 */
	 public function treeAction(User $user)
	 {
		$folders = $this->getDoctrine()->getRepository('WodaFSBundle:Folder')->findBy(array('user' => $user));
		$files = $this->getDoctrine()->getRepository('WodaFSBundle:XFile')->findBy(array('user' => $user));
		return array('user' => $user, 'current' => null, 'folders' => $folders, 'files' => $files);
	 }

	/**
	 * @Route("/admin/folder/{folder}", name="WodaFSBundle.Admin.folder")
	 * @Template("WodaFSBundle:Admin:fs.html.twig")
	 * @ParamConverter("folder", class="WodaFSBundle:Folder")
	 */
	public function folderAction(Folder $folder) {
		$folders = $this->getDoctrine()->getRepository('WodaFSBundle:Folder')->findBy(array('parent' => $folder));
		$files = $this->getDoctrine()->getRepository('WodaFSBundle:XFile')->findBy(array('parent' => $folder));
		return array('user' => $folder->getUser(), 'current' => $folder, 'folders' => $folders, 'files' => $files);
	}

	/**
	 * @Route("/admin/file/{file}", name="WodaFSBundle.Admin.file")
	 */
	public function fileAction(User $user, XFile $file) {
	}

	/**
	 * @Route("/admin/file/{file}/delete", name="WodaFSBundle.Admin.deleteFile")
	 */
	public function deleteFileAction(User $user, XFile $file) {
	}

	/**
	 * @Route("/admin/folder/{folder}/delete", name="WodaFSBundle.Admin.deleteFolder")
	 */
	public function deleteFolderAction(User $user, Folder $folder) {
	}
}
