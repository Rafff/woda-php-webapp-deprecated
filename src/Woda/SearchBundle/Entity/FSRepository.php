<?php

namespace Woda\SearchBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Woda\FSBundle\Entity\XFile;

class FSRepository extends EntityRepository
{
    public function searchAll($user, $term)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where (p.public = true OR p.user=:owner) AND LOWER(p.name) like :name')
            ->setParameter('owner', $user)
            ->setParameter('name', '%'.strtolower($term).'%')
            ->getResult());
    }

    public function search($user, $term, $order = array(), $limit = null)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where (p.public = true OR p.user=:owner) AND LOWER(p.name) like :name')
            ->setParameter('owner', $user)
            ->setParameter('name', '%'.strtolower($term).'%')
        ;

        if (!empty($limit)) {
            if (is_array($limit) && count($limit) == 2) {
                $query->setFirstResult($limit[0]);
                $query->setMaxResults($limit[1]);
            } else if (is_int($limit)) {
                $query->setMaxResults($limit);
            }
        }

        return ($query->getResult());
    }
}