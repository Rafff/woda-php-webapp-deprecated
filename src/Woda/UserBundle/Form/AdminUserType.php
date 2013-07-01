<?php

namespace Woda\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminUserType extends AbstractType
{
    private $edit;

    public function __construct($edit = false)
    {
        $this->edit = $edit;
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder
          ->add('username', 'text', array('label' => 'userbundle.profile.username', 'translation_domain' => 'woda'))
          ->add('email', 'email', array('label' => 'userbundle.profile.email', 'translation_domain' => 'woda'))
          //->add('password', 'password', array('label' => 'userbundle.profile.password', 'translation_domain' => 'woda', 'required' => !$this->edit))
          ->add('active', 'checkbox', array('required' => false))
        ;
    }

    public function getName()
    {
        return 'woda_userbundle_adminusertype';
    }
}
