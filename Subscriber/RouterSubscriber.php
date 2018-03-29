<?php

namespace EB78\CustomI18nRouterBundle\Subscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

class RouterSubscriber implements EventSubscriberInterface
{
    /** @var RouterInterface */
    private $router;
    /** @var Request */
    private $masterRequest;
    /**  @var string */
    private $available_locales;
    /** @var ContainerInterface */
    private $container;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            'kernel.request' => array(
                array('beforeRouter', 100)
            )
        );
    }

    /**
     * RouterSubscriber constructor.
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->router = $container->get('router');
        $this->masterRequest = $container->get('request_stack')->getMasterRequest();
        $this->available_locales = $container->getParameter('available_locales');
        $this->container = $container;
    }
    /**
     * @param GetResponseEvent $event
     */
    public function beforeRouter(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()) {
            $request = $event->getRequest();
            $paths = array_filter(explode('/', $request->getRequestUri()));
            if (\count($paths) > 0) {
                $path = current($paths);
                if ('_' !== $path[0]) {
                    if (\in_array($path, $this->available_locales, true)) {
                        $name = 'i18n_'.$path;
                        if ($this->container->hasParameter($name)) {
                            return $this->setLocaleAndMarket($request, $name);
                        }
                    }
                }
            }
            if ($this->container->hasParameter('default_market')) {
                $name = 'i18n_'.$this->container->getParameter('default_market');
                return $this->setLocaleAndMarket($request, $name);
            }
        } else {
            // retrieve Locale and Market From Master Request
            $request = $event->getRequest();
            if ($this->masterRequest->attributes->has('market')) {
                $market = $this->masterRequest->attributes->get('market');
                $request->attributes->set('market', $market);
                $request->attributes->set('_market', $market);
            }
            if ($this->masterRequest->attributes->has('locale')) {
                $locale = $this->masterRequest->attributes->get('locale');
                $request->setLocale($locale);
                $request->setDefaultLocale($locale);
                $request->attributes->set('locale', $locale);
                $request->attributes->set('_locale',$locale);
            }
        }
    }

    /**
     * @param Request $request
     * @param string $name
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    protected function setLocaleAndMarket(Request $request, string $name)
    {
        $configuration = $this->container->getParameter($name);
        $locale = (string)$configuration['locale'];
        $country = (string)$configuration['country'];
        $request->attributes->set('market', $locale.'-'.$country);
        $request->attributes->set('locale', $locale);
        $request->attributes->set('_locale',$locale);
        $request->attributes->set('_market',$locale.'-'.$country);
        if (null !== $request->getSession()) {
            $request->getSession()->set('_locale', $locale);
        }
        $request->setLocale($locale);
        $request->setDefaultLocale($locale);
    }
}
