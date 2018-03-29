<?php

namespace EB78\CustomI18nRouterBundle;

use EB78\CustomI18nRouterBundle\DependencyInjection\EB78CustomI18nRouterExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EB78CustomI18nRouterBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new EB78CustomI18nRouterExtension();
    }
}
