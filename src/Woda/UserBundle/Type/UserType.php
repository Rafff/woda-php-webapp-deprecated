<?php

namespace Woda\UserBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    private $edit;

    public function __construct($edit = false)
    {
        $this->edit = $edit;
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $options)
    {
        $formBuilder->add('email', 'email');
        $formBuilder->add('password', 'password', array('required' => ! $this->edit));
        $formBuilder->add('active', 'checkbox', array('required' => false));
    }

    public function getName()
    {
        return 'WodaUserBundle_UserType';
    }
}
