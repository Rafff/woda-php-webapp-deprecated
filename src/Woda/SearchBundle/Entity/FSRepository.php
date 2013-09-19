<?php

namespace Woda\SearchBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function search($user, $term, $order = array(), $limit = null, $type = "")
    {
        $query = $this->getQuery($user, $term, $type);
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

    private function getQuery($user, $term, $type)
    {
        if (!is_string($type)) {
            throw new \Exception('TODO');
        }

        $type = str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));

        $rMethod = new \ReflectionMethod($this, 'getQuery'.$type);
        return ($rMethod->invoke($this, $user, $term));
    }

    public function getQueryPrivateFile($user, $term)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where p.user=:owner AND LOWER(p.name) like :name')
            ->setParameter('owner', $user)
            ->setParameter('name', '%'.strtolower($term).'%')
        );
    }

    public function getQueryFile($user, $term)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where (p.public = true OR p.user=:owner) AND LOWER(p.name) like :name')
            ->setParameter('owner', $user)
            ->setParameter('name', '%'.strtolower($term).'%')
        );
    }

    public function getQueryMovie($user, $term)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where (p.public = true OR p.user=:owner) AND LOWER(p.name) like :name_mp4')
            ->setParameter('owner', $user)
            ->setParameters(
                    array(
                        'name_mp4' => '%'.strtolower($term).'%.mp4'
                        )
                    )
        );
    }

    public function getQueryMusic($user, $term)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where (p.public = true OR p.user=:owner) AND LOWER(p.name) like :name_mp3')
            ->setParameter('owner', $user)
            ->setParameters(
                    array(
                        'name_mp3' => '%'.strtolower($term).'%.mp3'
                        )
                    )
        );
    }

    public function getQueryPicture($user, $term)
    {
        return ($this->getEntityManager()
            ->createQuery('SELECT p FROM WodaFSBundle:XFile p where (p.public = true OR p.user=:owner) AND (LOWER(p.name) like :name_jpg OR LOWER(p.name) like :name_png)')
            ->setParameter('owner', $user)
            ->setParameters(
                    array(
                        'name_jpg' => '%'.strtolower($term).'%.jpg',
                        'name_png' => '%'.strtolower($term).'%.png'
                        )
                    )
        );
    }
}