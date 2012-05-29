<?php

namespace Woda\UserBundle\DataFixtures\ORM;

use Woda\UserBundle\Entity\User;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData implements ContainerAwareInterface, FixtureInterface
{
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load(ObjectManager $objectManager)
    {
        $user = new User();
        $user->setEmail('admin@localhost');
        
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword('test', $user->getSalt()));
        
        $objectManager->persist($user);
        $objectManager->flush();
    }
}
