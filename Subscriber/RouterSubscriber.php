<?php

namespace EB78\CustomI18nRouterBundle\Subscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

class RouterSubscriber implements EventSubscriberInterface
{
    /** @var Request */
    private $masterRequest;
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
                array('beforeRouter', 30)
            )
        );
    }

    /**
     * RouterSubscriber constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->masterRequest = $container->get('request_stack')->getMasterRequest();
        $this->container = $container;
    }

    /**
     * @param GetResponseEvent $event
     * @return void
     */
    public function beforeRouter(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($event->isMasterRequest()) {
            $currentMarket = explode('.', $request->attributes->get('_route'));
            $market = @end($currentMarket);
            if ($this->container->hasParameter('i18n_'.$market)) {
                $this->persistLocaleParameters($request, 'i18n_'.$market);
                return;
            }
            if ($this->container->hasParameter('default_locale')) {
                $name = 'i18n_'.$this->container->getParameter('default_locale');
                $this->persistLocaleParameters($request, $name);
                return;
            }
        } else {
            // retrieve parameters From Master Request
            $this->persistLocaleParameters($request, $this->masterRequest->attributes->get('configFile'));
        }
    }

    /**
     * @param Request $request
     * @param string $name
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    protected function persistLocaleParameters(Request $request, string $name)
    {
        $configuration = $this->container->getParameter($name);
        $configuredLocale = (string)$configuration['locale'];
        $prefix =  (string)$configuration['prefix'];
        list($locale, $country) = explode('_', $configuredLocale);

        // Market is a LCID string ( couple of locale and country like en-gb )
        $request->attributes->set('market', str_replace('_', '-', strtolower($configuredLocale)));
        $request->attributes->set('locale', $locale);
        $request->attributes->set('hasPrefix', !empty($prefix));
        $request->attributes->set('prefix', $prefix);
        $request->attributes->set('country', $country);
        $request->attributes->set('configFile', $name);
        $request->attributes->set('marketId', (string)$configuration['localeUId']);
        if (strtolower($locale) === strtolower($country)) {
            $request->attributes->set('translator_locale', $locale);
        } else {
            $request->attributes->set('translator_locale', $configuredLocale);
        }

        if (null !== $request->getSession()) {
            $request->getSession()->set('_locale', $locale);
        }
        $request->setLocale($locale);
        $request->setDefaultLocale($locale);
    }
}
