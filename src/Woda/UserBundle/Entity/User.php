<?php

namespace Woda\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;


/**
 * @ORM\Entity
 * @ORM\Table(name="UserBundle_Users")
 */

class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $email;
    
    /**
     * @ORM\Column(type="string", length=23)
     */
    protected $salt;
    
    /**
     * @ORM\Column(type="string", length=40)
     */
    protected $password;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;
    
    public function __construct()
    {
        $this->active = false;
        $this->salt = uniqid(null, true);
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
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getEmail();
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
     * Set email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
    
    /**
     * Get salt
     *
     * @return string
     */
    
    public function getSalt()
    {
        return $this->salt;
    }
    
    /**
     * Set salt
     *
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
        return $this;
    }
    
    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Set password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    /**
     * Set active
     *
     * @param boolean $active
     * @return User
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }
    
    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }
    
    /**
     * Erase credentials
     */
    public function eraseCredentials()
    {
    }
    
    /**
     * Compare two users
     *
     * @return boolean
     */
    public function equals(UserInterface $user)
    {
        return $this->getUsername() === $user->getUsername();
    }
}