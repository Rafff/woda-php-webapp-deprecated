<?php

namespace Woda\UserBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

use Woda\UserBundle\Entity\User;

class PasswordChangeTransformer implements DataTransformerInterface
{
    private $encoderFactory;
    private $user;

    public function __construct(EncoderFactoryInterface $encoderFactory, User $user)
    {
        $this->encoderFactory = $encoderFactory;
        $this->user = $user;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        if (empty($value) && $this->user->getPassword())
            return $this->user->getPassword();
        return $this->encoderFactory->getEncoder($user)->encodePassword($value, $user->getSalt());
    }
}
