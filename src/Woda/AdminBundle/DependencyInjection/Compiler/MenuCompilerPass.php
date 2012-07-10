<?php

namespace Woda\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MenuCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('woda_admin.menu_provider') as $id => $attributes) {
            $attributes = $attributes[0];
            if (isset($attributes['container'])) {
                $weight = isset($attributes['weight']) ? $attributes['weight'] : 0;
                $definition = $container->getDefinition($attributes['container']);
                $definition->addMethodCall('addContainer', array(new Reference($id), $weight));
            }
        }
    }
}
