<?php

namespace Woda\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="Cms")
 */
class Cms
{
    /**
     * @ORM\GeneratedValue
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", unique=true, length=150)
     */
    private $uri;

    /**
     *
     * @ORM\Column(type="boolean")
     */
    private $activated;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Cms
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set uri
     *
     * @param string $uri
     * @return Cms
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set content
     *
     * @param text $content
     * @return Cms
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get content
     *
     * @return text
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set activated
     *
     * @param boolean $activated
     * @return Cms
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;
        return $this;
    }

    /**
     * Get activated
     *
     * @return boolean
     */
    public function getActivated()
    {
        return $this->activated;
    }
}