<?php

namespace Woda\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(name="login", type="string", length=15, nullable=false, unique=true)
     */
    protected $login;

    /**
     * @ORM\Column(name="first_name", type="string", length=25, nullable=false)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=100, nullable=false)
     */
    protected $lastName;

    /**
     * @Assert\Email
     * @ORM\Column(type="string", length=100, unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $email;

    /**
     * @ORM\Column(name="pass_salt", type="string", length=23)
     */
    protected $salt;

    /**
     * @ORM\Column(name="pass_hash", type="string", length=32)
     */
    protected $password;

    /**
     * @Assert\MinLength(limit=0)
     * @Assert\MaxLength(limit=1)
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles;

    public function __construct()
    {
        $this->roles = array();
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
     * Set id
     *
     * @return User
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set first name
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set last name
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
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
     * Set roles
     *
     * @param array $roles
     * @return User
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles ? $this->roles : array();
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
        return $this->getId() === $user->getId();
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getLogin();
    }
}