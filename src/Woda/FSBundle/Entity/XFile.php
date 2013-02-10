<?php

namespace Woda\FSBundle\Entity;

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
     * @ORM\Column(name="name", type="string", length="15")
     */
    protected $name;

    /**
     * @ORM\Column(name="last_modification_time", type="datetime")
     */
    protected $lastModificationTime;

    /**
     * @ORM\ManyToOne(targetEntity="User", mappedBy="folders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", mappedBy="files")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $folder;
}
