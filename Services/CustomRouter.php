<?php

namespace EB78\CustomI18nRouterBundle\Services;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class CustomRouter extends Router
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /** @var RouteCollection */
    protected $collection;

    /** @var array */
    private $routes = [];

    /** @var Request */
    private $request;

    /**
     * @param ContainerInterface $container A ContainerInterface instance
     * @param mixed              $resource  The main resource to load
     * @param array              $options   An array of options
     * @param RequestContext     $context   The context
     */
    public function __construct(
        ContainerInterface $container,
        $resource,
        array $options = array(),
        RequestContext $context = null
    ) {
        $this->container = $container;
        $this->request = $container->get('request_stack')->getMasterRequest();
        parent::__construct($container, $resource, $options, $context);
        $this->getRouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function generate($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ('' !== $route && '_' !== $route) {
            $market = $this->request->attributes->get('_market');
            return parent::generate($route.'.'.$market, $parameters, $referenceType);
        }
        return parent::generate($route, $parameters, $referenceType);
    }
    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        parent::getRouteCollection();
        /** @var array $availableMarkets */
        $availableMarkets = $this->container->getParameter('available_locales');
        foreach ($availableMarkets as $market) {
            if ($this->container->hasParameter('router_'.$market)) {
                /** @var array $config */
                $config = $this->container->getParameter('router_'.$market);
                $prefix = $host = $country = $locale = '';
                foreach ($config as $key => $value) {
                    switch ($key) {
                        case 'prefix':
                            $prefix = $value;
                            break;
                        case 'host':
                            $host = $value;
                            break;
                        case 'locale':
                            $country = $value;
                            break;
                        case 'country':
                            $locale = $value;
                            break;
                        case 'routes':
                            $this->addRoutes($prefix, $locale, $country, $host, $value);
                            break;
                    }
                }
            }
        }
        if (\count($this->routes) > 0) {
            $this->routes = array_flip(array_flip($this->routes));
            foreach ($this->routes as $routeName) {
                $this->collection->remove($routeName);
            }
        }
        return $this->collection;
    }

    /**
     * @param string $prefix
     * @param string $locale
     * @param string $country
     * @param string $host
     * @param array $routes
     */
    private function addRoutes(
        string $prefix,
        string $locale,
        string $country,
        string $host,
        array $routes
    ) {
        foreach ($routes as $key => $route) {
            $currentRoute = $this->collection->get($key);
            if (null !== $currentRoute) {
                $currentRoute = clone $currentRoute;
                $currentRoute->setHost($host);
                if ('' !== $prefix) {
                    $currentRoute->setPath($prefix.'/'.ltrim($route, '/'));
                } else {
                    $currentRoute->setPath($route);
                }
                $this->routes[] = $key;
                $this->collection->add($key.'.'.$locale.'-'.$country, $currentRoute);
            }
        }
    }
}
