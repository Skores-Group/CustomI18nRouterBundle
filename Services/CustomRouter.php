<?php

namespace EB78\CustomI18nRouterBundle\Services;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
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

    /** @var RouteCollection */
    protected $priorityCollection;

    /** @var RouteCollection */
    protected $defaultCollection;

    /** @var array */
    private $removedRoutes = [];

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
    public function generate($route, $params = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($this->request === null) {
            $this->request = $this->container->get('request_stack')->getMasterRequest();
        }
        if ($this->request !== null) {
            $market = $this->request->attributes->get('market');
            if (\in_array($route, $this->routes, true)) {
                return parent::generate($route, $params, $referenceType);
            }
            if (\in_array($route.'.'.$market, $this->routes, true)) {
                return parent::generate($route.'.'.$market, $params, $referenceType);
            }

            $end = substr($route, \strlen($route) - \strlen($market), \strlen($market));
            if ($end === $market) {
                $route = substr($route, 0, \strlen($route)-\strlen($market) - 1);
            }
        }

        return parent::generate($route, $params, $referenceType);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        $this->collection = new RouteCollection();

        // Load default current routes
        $this->defaultCollection = $this->container->get('routing.loader')->load(
            $this->resource,
            $this->options['resource_type']
        );

        // filter routes ( _ and exceptions )
        $this->collection->addCollection($this->filterRoutes());

        // Find config for available locales
        $availableMarkets = [];
        $list = $this->container->getParameter('available_locales');
        foreach ($list as $market) {
            if ($this->container->hasParameter('i18n_' . $market)) {
                $config = $this->container->getParameter('i18n_'.$market);
                $availableMarkets[$market] = $config;
            }
        }

        // Duplicate other routes for alternative markets
        foreach ($this->defaultCollection as $key => $route) {
            foreach ($availableMarkets as $market => $config) {
                // find route into config
                $prefix = $config['prefix'];
                $configuredLocale = $config['locale'];
                $parts = explode('_', $configuredLocale);
                $locale = $parts[0];
                $country =  strtolower($parts[1]);
                $host = $config['host'];
                $path = $route->getPath();
                if (isset($config['routes'][$key])) {
                    $path = $config['routes'][$key];
                }
                $currentRoute = clone $route;
                if (strpos($currentRoute->getHost(), '{') === false) {
                    $currentRoute->setHost($host);
                }

                if ('' !== $prefix) {
                    $currentRoute->setPath($prefix.'/'.ltrim($path, '/'));
                } else {
                    $currentRoute->setPath($path);
                }

                $this->removedRoutes[] = $key;
                $this->routes[] = $key.'.'.$locale.'-'.$country;
                $this->collection->add($key.'.'.$locale.'-'.$country, $currentRoute);
            }
        }

        // remove routes
        $this->removeRoutes();

        // Resolve parameters
        $this->resolveParameters($this->collection);

        return $this->collection;
    }

    /**
     * Remove old routes from RouteCollection
     */
    private function removeRoutes()
    {
        if (\count($this->removedRoutes) > 0) {
            $this->removedRoutes = array_flip(array_flip($this->removedRoutes));
            foreach ($this->removedRoutes as $routeName) {
                $this->collection->remove($routeName);
            }
        }
    }

    /**
     * @return RouteCollection
     */
    private function filterRoutes(): RouteCollection
    {
        $tmpCollection = new RouteCollection();
        foreach ($this->defaultCollection as $key => $route) {
            if ($key[0] === '_' || stripos($key, 'pages_exceptions') === 0) {
                $tmpCollection->add($key, $route);
                $this->routes[] = $key;
                $this->defaultCollection->remove($key);
            }
        }
        return $tmpCollection;
    }

    /**
     * Replaces placeholders with service container parameter values in:
     * - the route defaults,
     * - the route requirements,
     * - the route path,
     * - the route host,
     * - the route schemes,
     * - the route methods.
     * @param RouteCollection $collection
     */
    private function resolveParameters(RouteCollection $collection)
    {
        foreach ($collection as $route) {
            foreach ($route->getDefaults() as $name => $value) {
                $route->setDefault($name, $this->resolve($value));
            }

            foreach ($route->getRequirements() as $name => $value) {
                if ('_scheme' === $name || '_method' === $name) {
                    continue; // ignore deprecated requirements to not trigger deprecation warnings
                }

                $route->setRequirement($name, $this->resolve($value));
            }

            $route->setPath($this->resolve($route->getPath()));
            $route->setHost($this->resolve($route->getHost()));

            $schemes = array();
            foreach ($route->getSchemes() as $scheme) {
                $schemes = array_merge($schemes, explode('|', $this->resolve($scheme)));
            }
            $route->setSchemes($schemes);

            $methods = array();
            foreach ($route->getMethods() as $method) {
                $methods = array_merge($methods, explode('|', $this->resolve($method)));
            }
            $route->setMethods($methods);
            $route->setCondition($this->resolve($route->getCondition()));
        }
    }

    /**
     * Recursively replaces placeholders with the service container parameters.
     *
     * @param mixed $value The source which might contain "%placeholders%"
     *
     * @return mixed The source with the placeholders replaced by the container
     *               parameters. Arrays are resolved recursively.
     *
     * @throws ParameterNotFoundException When a placeholder does not exist as a container parameter
     * @throws RuntimeException           When a container value is not a string or a numeric value
     */
    private function resolve($value)
    {
        if (\is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolve($val);
            }

            return $value;
        }

        if (!\is_string($value)) {
            return $value;
        }

        $container = $this->container;

        $escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($container, $value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            $resolved = $container->getParameter($match[1]);

            if (\is_string($resolved) || is_numeric($resolved)) {
                return (string) $resolved;
            }

            throw new RuntimeException(
                sprintf(
                    'The container parameter "%s", used in the route configuration value "%s", '.
                    'must be a string or numeric, but it is of type %s.',
                    $match[1],
                    $value,
                    \gettype($resolved)
                )
            );
        }, $value);

        return str_replace('%%', '%', $escapedValue);
    }
}
