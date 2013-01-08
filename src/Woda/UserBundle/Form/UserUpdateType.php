<?php

namespace Woda\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', array('label' => 'userbundle.profile.firstname', 'translation_domain' => 'woda'))
            ->add('lastname', 'text', array('label' => 'userbundle.profile.lastname', 'translation_domain' => 'woda'))
        ;
    }

    public function getName()
    {
        return 'woda_userbundle_userupdatetype';
    }
}
