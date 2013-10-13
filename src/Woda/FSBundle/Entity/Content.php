<?php

namespace Woda\FSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Content")
 */
class Content
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="XFile", mappedBy="content_hash", cascade={"delete"})
     * @ORM\Column(name="content_hash", type="string", length=256)
     */
    private $content_hash;

    /**
     * @ORM\Column(name="crypt_key", type="string", length=64)
     */
    private $crypt_key;

    /**
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @ORM\Column(name="file_type", type="string", length=255)
     */
    private $file_type;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getContentHash()
    {
        return $this->content_hash;
    }

    public function setContentHash($contentHash)
    {
        $this->content_hash = $contentHash;
    }

    /**
     * Set crypt_key
     *
     * @param string $cryptKey
     * @return Content 
     */
    public function setCryptKey($cryptKey)
    {
        $this->crypt_key = bin2hex($cryptKey);
    }

    /**
     * Get crypt_key
     *
     * @return string 
     */
    public function getCryptKey()
    {
        return hex2bin($this->crypt_key);
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return Content
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set file_type
     *
     * @param string $fileType
     * @return Content
     */
    public function setFileType($fileType)
    {
        $this->file_type = $fileType;
        return $this;
    }

    /**
     * Get file_type
     *
     * @return string 
     */
    public function getFileType()
    {
        return $this->file_type;
    }
}