<?php

namespace Woda\AdministrationCmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CmsType extends AbstractType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('uri')
            ->add('content')
        ;
    }

    public function getName()
    {
        return 'woda_administrationcmsbundle_cmstype';
    }
}
