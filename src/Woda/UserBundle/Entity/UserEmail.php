<?php

namespace Woda\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Woda\UserBundle\Entity\User as User;

/**
 * @ORM\Entity
 */
class UserEmail
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
     * @Assert\Email
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $email;

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
        $this->token = uniqid(null, true);
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
     * @return UserEmail
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
     * @return UserEmail
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
     * Set email
     *
     * @return UserEmail
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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