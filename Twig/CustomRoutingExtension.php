<?php

namespace EB78\CustomI18nRouterBundle\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class CustomRoutingExtension extends RoutingExtension
{
    /** @var Request */
    private $masterRequest;
    /**  @var ContainerInterface */
    private $container;

    /** Default locale */
    const DEFAULT_LOCALE = 'fr-fr';

    /**
     * CustomRoutingExtension constructor.
     * @param ContainerInterface $container
     * @param RequestStack $request
     * @param RouterInterface $router
     */
    public function __construct(ContainerInterface $container, RequestStack $request, RouterInterface $router)
    {
        $this->container = $container;
        $this->masterRequest = $request->getMasterRequest();
        parent::__construct($router);
    }

    /**
     * @param string $name
     * @param array $parameters
     * @param bool $relative
     * @return string
     */
    public function getPath($name, $parameters = array(), $relative = false)
    {
        try {
            $locale = self::DEFAULT_LOCALE;
            if (isset($parameters['_locale'])) {
                $locale = $parameters['_locale'];
            }

            if (false === strpos($name, '_')) {
                if ($this->container !== null && $this->container->hasParameter('default_locale')) {
                    $locale = $this->container->getParameter('default_locale');
                }
                if ($this->masterRequest !== null &&
                    $this->masterRequest->attributes !== null &&
                    $this->masterRequest->attributes->has('market')
                ) {
                    $locale = $this->masterRequest->attributes->get('market');
                }
                return parent::getPath($name.'.'.$locale, $parameters, $relative);
            }
        } catch (\Exception $e) {
        }
        return parent::getPath($name, $parameters, $relative);
    }
}
