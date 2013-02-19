<?php

namespace Woda\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SecurityRecoveryPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'Les mots de passe doivent correspondre',
                    'options' => array('required' => true),
                    'first_options'  => array('label' => 'userbundle.profile.password', 'translation_domain' => 'woda'),
                    'second_options' => array('label' => 'userbundle.profile.password_validation', 'translation_domain' => 'woda'),
                )
            )
        ;
    }

    public function getName()
    {
        return 'woda_userbundle_securityrecoverypasswordtype';
    }
}
