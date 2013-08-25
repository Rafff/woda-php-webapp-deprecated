<?php

namespace Woda\SearchBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Woda\FSBundle\Entity\XFile;

class FSRepository extends EntityRepository
{
    public function findAllLikeName($name)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where LOWER(p.name) like :name')
            ->setParameter('name', '%'.strtolower($name).'%')
            ->getResult());
    }

    public function findFileLikeName($name, $order = array(), $limit = null)
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where LOWER(p.name) like :name')
            ->setParameter('name', '%'.strtolower($name).'%')
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