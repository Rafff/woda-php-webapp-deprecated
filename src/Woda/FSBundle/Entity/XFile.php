<?php

namespace Woda\FSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
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
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="file_hash", type="string", length=256)
     */
    protected $fileHash;

    /**
     * @ORM\Column(name="file_type", type="string", length=255)
     */

    protected $fileType;

    /**
     * @ORM\Column(name="last_modification_time", type="datetime")
     */
    protected $lastModificationTime;

    /**
     * @ORM\ManyToOne(targetEntity="Woda\UserBundle\Entity\User", inversedBy="folders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;


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

    public function getFileHash()
    {
        return $this->fileHash;
    }

    public function setFileHash($fileHash)
    {
        $this->fileHash = $fileHash;
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