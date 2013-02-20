<?php

namespace Woda\SearchBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Woda\UserBundle\Entity\User;

class UserRepository extends EntityRepository
{
    public function findAllLikeLogin($login)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaUserBundle:User p where p.login like :login')
            ->setParameter('login', '%'.$login.'%')
            ->getResult());
    }
}