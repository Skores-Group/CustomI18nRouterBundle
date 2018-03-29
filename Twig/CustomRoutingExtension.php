<?php

namespace EB78\CustomI18nRouterBundle\Twig;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class CustomRoutingExtension extends RoutingExtension
{
    /** @var Request */
    private $masterRequest;
    /**  @var ContainerInterface */
    private $container;

    /**
     * CustomRoutingExtension constructor.
     * @param ContainerInterface $container
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->masterRequest = $container->get('request_stack')->getMasterRequest();
        parent::__construct($container->get('router'));
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
            if (0 === strpos($name, '_')) {
                return parent::getPath($name, $parameters, $relative);
            }
            $locale = 'fr';
            $market = 'fr-fr';
            if ($this->container->hasParameter('locale')) {
                $locale = $this->container->getParameter('locale');
            }
            if ($this->container->hasParameter('default_market')) {
                $market = $this->container->getParameter('default_market');
            }
            if ($this->masterRequest->attributes->has('_market')) {
                $market = $this->masterRequest->attributes->get('_market');
                $locale = substr($market, 0, 2);
            } elseif ($this->masterRequest->attributes->has('_locale')) {
                $locale = $this->masterRequest->attributes->get('_locale');
                $locale = substr($locale, 0, 2);
            }
            $parameters['locale'] = $locale;
            $uri = parent::getPath($name.'.'.$locale.'.'.$market, $parameters, $relative);
        } catch (\Exception $e) {
            $uri = parent::getPath($name, $parameters, $relative);
        }
        return $uri;
    }
}
