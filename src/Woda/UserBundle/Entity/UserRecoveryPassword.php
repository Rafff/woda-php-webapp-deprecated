<?php

namespace Woda\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Woda\UserBundle\Entity\User as User;

/**
 * @ORM\Entity
 */
class UserRecoveryPassword
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Woda\UserBundle\Entity\User")
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=23, nullable=false)
     */
    protected $token;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @Assert\MinLength(limit=0)
     * @Assert\MaxLength(limit=1)
     * @ORM\Column(type="boolean")
     */
    protected $available;

    public function __construct()
    {
        $this->generateNewToken();
        $this->date = new \DateTime('now');
        $this->available = true;
    }

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
     * Set user
     *
     * @return UserPassword
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set token
     *
     * @return UserPassword
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * generate a new token
     *
     * @return string
     */
    public function generateNewToken()
    {
        $this->token = uniqid(null, true);
        return ($this->token);
    }

    /**
     * Set date
     *
     * @return UserPassword
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set available
     *
     * @return UserPassword
     */
    public function setAvailable($available)
    {
        $this->available = $available;
        return $this;
    }

    /**
     * Get available
     *
     * @return \DateTime
     */
    public function getAvailable()
    {
        return $this->available;
    }
}