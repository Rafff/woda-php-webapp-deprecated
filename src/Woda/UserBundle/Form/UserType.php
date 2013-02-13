<?php

namespace Woda\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login', 'text', array('label' => 'userbundle.profile.login', 'translation_domain' => 'woda'))
            ->add('email', 'repeated', array(
                    'type' => 'email',
                    'invalid_message' => 'Les adresses mails doivent correspondre',
                    'options' => array('required' => true),
                    'first_options'  => array('label' => 'userbundle.profile.email', 'translation_domain' => 'woda'),
                    'second_options' => array('label' => 'userbundle.profile.email_validation', 'translation_domain' => 'woda'),
                )
            )
            ->add('password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'Les mots de passe doivent correspondre',
                    'options' => array('required' => true),
                    'first_options'  => array('label' => 'userbundle.profile.password', 'translation_domain' => 'woda'),
                    'second_options' => array('label' => 'userbundle.profile.password_validation', 'translation_domain' => 'woda'),
                )
            )
            ->add('firstname', 'text', array('label' => 'userbundle.profile.firstname', 'translation_domain' => 'woda'))
            ->add('lastname', 'text', array('label' => 'userbundle.profile.lastname', 'translation_domain' => 'woda'))
        ;
    }

    public function getName()
    {
        return 'woda_userbundle_usertype';
    }
}
