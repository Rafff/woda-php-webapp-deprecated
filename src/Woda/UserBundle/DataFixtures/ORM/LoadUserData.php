<?php

namespace Woda\UserBundle\DataFixtures\ORM;

use Woda\UserBundle\Entity\User;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    const USER_COUNT = 15;

    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $objectManager)
    {
        $firstnames = array('John', 'Gerard', 'Sylvie', 'Aude', 'Jules');
        $lastnames = array('Paillote', 'Bar', 'Wiz', 'Superyop');

        $user = new User();
        $user->setLogin('admin');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setEmail('admin@localhost');
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword('test', $user->getSalt()));
        $user->setRoles(array('ROLE_USER', 'ROLE_ADMIN'));
        $user->setActive(true);
        $objectManager->persist($user);

        for ( $t = 0; $t < self::USER_COUNT; ++ $t ) {
            $user = new User();
            $user->setLogin('user' . $t);
            $user->setFirstName($firstnames[$t % count($firstnames)]);
            $user->setLastName($lastnames[$t % count($lastnames)]);
            $user->setEmail('user' . $t . '@foobar');
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $user->setPassword($encoder->encodePassword('test', $user->getSalt()));
            $user->setRoles(array('ROLE_USER'));
            $user->setActive(true);
            $this->setReference('user-' . $t, $user);
            $objectManager->persist($user);
        }

        $objectManager->flush();
    }

    public function getOrder()
    {
        return 0;
    }
}
