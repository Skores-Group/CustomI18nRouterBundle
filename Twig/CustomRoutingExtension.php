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

    /** Default market ( market is a couple of locale and country ) */
    const DEFAULT_MARKET = 'fr-fr';

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
            if (false === strpos($name, '_')) {
                $market = self::DEFAULT_MARKET;
                if ($this->container !== null && $this->container->hasParameter('default_locale')) {
                    $market = $this->container->getParameter('default_locale');
                }
                if ($this->masterRequest !== null &&
                    $this->masterRequest->attributes !== null &&
                    $this->masterRequest->attributes->has('market')
                ) {
                    $market = $this->masterRequest->attributes->get('market');
                }
                return parent::getPath($name.'.'.$market, $parameters, $relative);
            }
        } catch (\Exception $e) {
        }
        return parent::getPath($name, $parameters, $relative);
    }
}
