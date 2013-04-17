<?php

namespace Woda\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AccountEditEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('new_email', 'email', array('label' => 'userbundle.profile.email_new', 'translation_domain' => 'woda'))
        ;
    }

    public function getName()
    {
        return 'woda_userbundle_accounteditemailtype';
    }
}
