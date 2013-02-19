<?php

namespace Woda\FSBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Folder")
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
     * @ORM\Column(name="name", type="string", length=20)
     */
    protected $name;

    /**
     * @ORM\Column(name="last_modification_time", type="datetime")
     **/
    protected $lastModificationTime;

    /**
     * @ORM\ManyToOne(targetEntity="User", mappedBy="folders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent")
     */
    protected $folders;

    /**
     * @ORM\OneToMany(targetEntity="XFile", mappedBy="parent")
     */
    protected $files;
}
