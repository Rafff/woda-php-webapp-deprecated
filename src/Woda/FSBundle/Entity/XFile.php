<?php

namespace Woda\FSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Woda\SearchBundle\Entity\FSRepository")
 * @ORM\Table(name="XFile")
 */

class XFile
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="files")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Content", inversedBy="content_hash")
     * @ORM\Column(name="content_hash", type="string", length=256)
     */
    protected $content_hash;

    /**
     * @ORM\Column(name="last_modification_time", type="datetime")
     */
    protected $lastModificationTime;

    /**
     * @ORM\ManyToOne(targetEntity="Woda\UserBundle\Entity\User", inversedBy="folders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(name="public", type="boolean")
     */
    protected $public;
    public function setPublic($public){ $this->public = $public; return $this; }
    public function isPublic($public){ return $this->public; }

    public function __construct()
    {
        $this->public = false;
    }

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

    // /**
    //  * Set content hash
    //  *
    //  * @param Woda\FSBundle\Entity\Content $contentHash
    //  * @return XFile
    // */
    // public function setContentHash(\Woda\FSBundle\Entity\Content $contentHash = null)
    // {
    //     $this->content_hash = $contentHash;
    // }

    // /**
    //  * Get ContentHash
    //  *
    //  * @return Woda\FSBundle\Entity\Content
    //  */
    // public function getContentHash()
    // {
    //     return $this->content_hash;
    // }

    public function getContentHash()
    {
        return $this->content_hash;
    }

    public function setContentHash($contentHash)
    {
        $this->content_hash = $contentHash;
    }

    public function getFileType()
    {
        return $this->fileType;
    }

    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
    }

    public function getLastModificationTime()
    {
        return $this->lastModificationTime;
    }

    public function setLastModificationTime($mod)
    {
        $this->lastModificationTime = $mod;
    }

    /**
     * Set user
     *
     * @param Woda\UserBundle\Entity\User $user
     * @return XFile
     */
    public function setUser(\Woda\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return Woda\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}