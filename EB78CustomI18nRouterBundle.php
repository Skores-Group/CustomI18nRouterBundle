<?php

namespace EB78\CustomI18nRouterBundle;

use EB78\CustomI18nRouterBundle\DependencyInjection\EB78CustomI18nRouterExtension;
use EB78\CustomI18nRouterBundle\Services\CustomCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EB78CustomI18nRouterBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new EB78CustomI18nRouterExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CustomCompilerPass());
    }
}
