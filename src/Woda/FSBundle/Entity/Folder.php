<?php

namespace Woda\FSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Folder")
 * @ORM\Entity(repositoryClass="Woda\FSBundle\Entity\FolderRepository")
 */

class Folder
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="last_modification_time", type="datetime")
     **/
    protected $lastModificationTime;

    /**
     * @ORM\ManyToOne(targetEntity="Woda\UserBundle\Entity\User", inversedBy="folders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent")
     */
    protected $folders;

    /**
     * @ORM\OneToMany(targetEntity="XFile", mappedBy="parent", cascade={"delete"})
     */
    protected $files;

    /**
     * @ORM\Column(name="public", type="boolean")
     */
    protected $public;
    public function setPublic($public){ $this->public = $public; return $this; }
    public function isPublic($public){ return $this->public; }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLastModificationTime()
    {
        return $this->lastModificationTime;
    }

    public function setLastModificationTime($mod)
    {
        $this->lastModificationTime = $mod;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getFolders()
    {
        return $this->folders;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function __construct()
    {
        $this->public = false;
        $this->folders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add folders
     *
     * @param Woda\FSBundle\Entity\Folder $folders
     * @return Folder
     */
    public function addFolder(\Woda\FSBundle\Entity\Folder $folders)
    {
        $this->folders[] = $folders;
        return $this;
    }

    /**
     * Remove folders
     *
     * @param Woda\FSBundle\Entity\Folder $folders
     */
    public function removeFolder(\Woda\FSBundle\Entity\Folder $folders)
    {
        $this->folders->removeElement($folders);
    }

    /**
     * Add files
     *
     * @param Woda\FSBundle\Entity\XFile $files
     * @return Folder
     */
    public function addFile(\Woda\FSBundle\Entity\XFile $files)
    {
        $this->files[] = $files;
        return $this;
    }

    /**
     * Remove files
     *
     * @param Woda\FSBundle\Entity\XFile $files
     */
    public function removeFile(\Woda\FSBundle\Entity\XFile $files)
    {
        $this->files->removeElement($files);
    }
}