<?php

namespace Woda\FSBundle\DataFixtures\ORM;

use Woda\FSBundle\Entity\Folder;
use Woda\FSBundle\Entity\XFile;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadFSData implements ContainerAwareInterface, FixtureInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $objectManager)
    {
        $repository = $this->container
                           ->get('doctrine')
                           ->getManager()
                           ->getRepository('WodaUserBundle:User');

        $user = $repository->find(1);

        $root = new Folder();
        $root->setParent(null);
        $root->setName('');
        $root->setUser($user);
        $root->setLastModificationTime(new \Datetime());
        $objectManager->persist($root);

        $folder = new Folder();
        $folder->setParent($root);
        $folder->setName('Super Dossier');
        $folder->setUser($user);
        $folder->setLastModificationTime(new \Datetime());
        $objectManager->persist($folder);

        $file = new XFile();
        $file->setParent($root);
        $file->setName('Super Fichier');
        $file->setLastModificationTime(new \Datetime());
        $objectManager->persist($file);

        $file = new XFile();
        $file->setParent($folder);
        $file->setName('Super Fichier 1');
        $file->setLastModificationTime(new \Datetime());
        $objectManager->persist($file);

        $file = new XFile();
        $file->setParent($folder);
        $file->setName('Super Fichier 2');
        $file->setLastModificationTime(new \Datetime());
        $objectManager->persist($file);

        $objectManager->flush();
    }
}
