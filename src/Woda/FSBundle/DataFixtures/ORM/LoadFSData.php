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
        $root->setName('Test');
        $root->setUser($user);
        $root->setLastModificationTime(new \Datetime());
        $objectManager->persist($root);

        $folder = new Folder();
        $folder->setParent($root);
        $file->setUser($user);
        $folder->setName('Super Dossier');
        $folder->setUser($user);
        $folder->setLastModificationTime(new \Datetime());
        $objectManager->persist($folder);

        $file = new XFile();
        $file->setParent($root);
        $file->setUser($user);
        $file->setName('Prime.c');
        $file->setFileHash('c1fcd97ad07525a76cb43324339d0f3fa4908cedf45fae3f5287ca6507e7fe5a');
        $file->setFileType('text/x-c++');
        $file->setLastModificationTime(new \Datetime());
		$file->setUser($user);
        $objectManager->persist($file);

        $file = new XFile();
        $file->setParent($folder);
        $file->setName('Super Fichier 1');
        $file->setLastModificationTime(new \Datetime());
		$file->setUser($user);
        $objectManager->persist($file);

        $file = new XFile();
        $file->setParent($folder);
        $file->setName('Super Fichier 2');
        $file->setLastModificationTime(new \Datetime());
		$file->setUser($user);
        $objectManager->persist($file);

        $objectManager->flush();
    }
}
