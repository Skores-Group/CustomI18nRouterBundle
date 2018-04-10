<?php
/**
 * Created by PhpStorm.
 * User: manu
 * Date: 10/04/2018
 * Time: 01:30
 */

namespace EB78\CustomI18nRouterBundle\tests\Subscriber;

use EB78\CustomI18nRouterBundle\Subscriber\RouterSubscriber;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RouterSubscriberTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @var m\MockInterface */
    private $container;
    /** @var m\MockInterface */
    private $request;
    /** @var RouterSubscriber */
    private $subscriber;

    public function setUp()
    {
        $this->container = m::mock(ContainerInterface::class);
        $requestStack = m::mock(RequestStack::class);
        $this->request = m::mock(Request::class);
        $requestStack
            ->shouldReceive('getMasterRequest')
            ->once()
            ->andReturn($this->request);
        $this->subscriber = new RouterSubscriber(
            $this->container,
            $requestStack
        );
    }

    public function testGetSubscriberEventsShouldReturnAnArray()
    {
        $eventList = [
            'kernel.request' => [
                ['beforeRouter', 30]
            ]
        ];

        $result = $this->subscriber::getSubscribedEvents();
        $this->assertSame($result, $eventList);
    }

    public function testBeforeRouterShouldPersistI18nConfigurationForMasterRequest()
    {
        $event = m::mock(GetResponseEvent::class);
        $request = m::mock(Request::class);
        $request->attributes = m::mock(ParameterBag::class);

        $locale = 'fr-fr';
        $route = 'home.index.'.$locale;

        $event
            ->shouldReceive('getRequest')
            ->once()
            ->andReturn($request);

        $event
            ->shouldReceive('isMasterRequest')
            ->once()
            ->andReturn(true);

        $request
            ->attributes
            ->shouldReceive('get')
            ->once()
            ->with('_route')
            ->andReturn($route);

        $this
            ->container
            ->shouldReceive('hasParameter')
            ->once()
            ->andReturn(true);

        $this->persistLocaleParameters($request, 'i18n_'.$locale);
        $result = $this->subscriber->beforeRouter($event);
        $this->assertSame('fr-fr', $result['market']);
        $this->assertSame('fr', $result['locale']);
        $this->assertFalse($result['hasPrefix']);
        $this->assertSame('', $result['prefix']);
        $this->assertSame('fr', $result['country']);
        $this->assertSame('i18n_fr-fr', $result['configFile']);
        $this->assertSame(1, $result['marketId']);
    }

    public function persistLocaleParameters(m\MockInterface $request, string $name)
    {
        $configuration = [
            'locale' => 'fr_FR',
            'prefix' => '',
            'localeUId' => 1,
        ];

        $locale = 'fr';
        $country = 'fr';
        $this
            ->container
            ->shouldReceive('getParameter')
            ->once()
            ->with($name)
            ->andReturn($configuration);

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('market', $locale.'-'.$country)
            ->andReturn();

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('locale', $locale)
            ->andReturn();

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('hasPrefix', false)
            ->andReturn();

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('prefix', '')
            ->andReturn();

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('country', strtoupper($country))
            ->andReturn();

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('configFile', $name)
            ->andReturn();

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('marketId', 1)
            ->andReturn();

        $request
            ->attributes
            ->shouldReceive('set')
            ->with('translator_locale', $locale)
            ->andReturn();

        $session = m::mock(SessionInterface::class);
        $request
            ->shouldReceive('getSession')
            ->with()
            ->andReturn($session);

        $session
            ->shouldReceive('set')
            ->once()
            ->with('_locale', $locale)
            ->andReturn();

        $request
            ->shouldReceive('setLocale')
            ->once()
            ->with($locale);

        $request
            ->shouldReceive('setDefaultLocale')
            ->once()
            ->with($locale);

        $result = [
            'locale' => 'fr',
            'market' => 'fr-fr',
            'hasPrefix' => false,
            'prefix' => '',
            'country' => 'fr',
            'configFile' => $name,
            'marketId' => 1
        ];
        $request
            ->attributes
            ->shouldReceive('all')
            ->andReturn($result);
    }
}