<?php

namespace Woda\SearchBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Woda\UserBundle\Entity\User;

class UserRepository extends EntityRepository
{
    public function search($login, $order = array(), $limit = null)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT p FROM WodaUserBundle:User p where p.login like :login')
            ->setParameter('login', '%'.$login.'%')
        ;

        $count = count(new Paginator($query, true));

        if (!empty($limit)) {
            if (is_array($limit) && count($limit) == 2) {
                $query->setFirstResult($limit[0]);
                $query->setMaxResults($limit[1]);
            } else if (is_int($limit)) {
                $query->setMaxResults($limit);
            }
        }

        $o = new \stdClass();
        $o->result = $query->getResult();
        $o->count = $count;
        return ($o);
    }
}