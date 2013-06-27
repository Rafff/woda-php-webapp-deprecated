<?php

namespace Woda\FSBundle\DataFixtures\ORM;

use Woda\FSBundle\Entity\Folder;
use Woda\FSBundle\Entity\XFile;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Woda\UserBundle\DataFixtures\ORM\LoadUserData;

class LoadFSData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    const FOLDER_COUNT_PER_USER = 5;
    const FILE_COUNT_PER_FOLDER = 10;

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $objectManager)
    {
        $exts = array('jpg', 'png', 'mp3', 'css', 'c', 'cpp');

        for ($t = 0; $t < LoadUserData::USER_COUNT; ++ $t) {
            $user = $this->getReference('user-' . $t);

            for ($u = 0; $u < self::FOLDER_COUNT_PER_USER; ++ $u) {
                $folder = new Folder();
                $folder->setParent(null);
                $folder->setUser($user);
                $folder->setName('Superfolder #' . $u);
                $folder->setLastModificationTime(new \Datetime());
                $objectManager->persist($folder);

                for ( $v = 0; $v < self::FILE_COUNT_PER_FOLDER; ++ $v) {
                    $file = new XFile();
                    $file->setParent($folder);
                    $file->setUser($user);
                    $file->setName('Superfile #' . $v . '.' . $exts[($v + $u) % count($exts)]);
                    $file->setFileHash('lolhash');
                    $file->setFileType('text/plain');
                    $file->setLastModificationTime(new \Datetime());
                    $objectManager->persist($file);
                }
            }
        }

        $objectManager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
