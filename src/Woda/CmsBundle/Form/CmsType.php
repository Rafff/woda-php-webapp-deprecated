<?php

namespace Woda\CmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CmsType extends AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array('required' => false))
            ->add('uri')
            ->add('activated', 'checkbox', array('required' => false))
            ->add('content', 'textarea', array('required' => false));
        ;
    }

    public function getName()
    {
        return ('woda_cmsbundle_cmstype');
    }
}
