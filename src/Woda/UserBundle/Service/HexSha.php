<?php

namespace Woda\UserBundle\Service;

use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class HexSha implements PasswordEncoderInterface
{

    public function encodePassword($raw, $salt)
    {
        return hash('sha256', $salt . $raw);
    }

    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $encoded === $this->encodePassword($raw, $salt);
    }

}

?>