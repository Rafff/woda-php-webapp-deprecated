<?php

namespace Woda\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Woda\SearchBundle\Entity\UserRepository")
 * @ORM\Table(name="`user`")
 */
class User implements AdvancedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="login", type="string", length=15, unique=true)
     */
    protected $login;

    /**
     * @Assert\Email
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $email;

    /**
     * @ORM\Column(name="pass_salt", type="string", length=64)
     */
    protected $salt;

    /**
     * @ORM\Column(name="pass_hash", type="string", length=64)
     */
    protected $password;

    /**
     * @Assert\MinLength(limit=0)
     * @Assert\MaxLength(limit=1)
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @Assert\MinLength(limit=0)
     * @Assert\MaxLength(limit=1)
     * @ORM\Column(type="boolean")
     */
    protected $locked;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles;

    /**
     * @ORM\ManyToMany(targetEntity="Woda\FSBundle\Entity\XFile")
     */
    protected $favorites;

    private function randomSalt() {
        $string = "";
        $chaine = "abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
        srand((double)microtime()*1000000);
        for($i=0; $i<32; $i++) {
            $string .= $chaine[rand()%strlen($chaine)];
        }
        return $string;
    }

    public function __construct()
    {
        $this->roles = array();
        $this->active = false;
        $this->locked = false;
        $this->salt = bin2hex($this->randomSalt());

        $this->favorites = array();
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
     * Set locked
     *
     * @param boolean $active
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
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
    public function equals(AdvancedUserInterface $user)
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
        return $this->login;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->active;
    }

    public function getFavorites()
    {
        return $this->favorites;
    }

    public function addFavorite(\Woda\FSBundle\Entity\XFile $file)
    {
        $this->favorites[] = $file;
        return $this;
    }

    public function removeFavorite(\Woda\FSBundle\Entity\XFile $file)
    {
        $this->favorites->removeElement($file);
    }
}