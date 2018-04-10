<?php
/**
 * Created by PhpStorm.
 * User: manu
 * Date: 10/04/2018
 * Time: 01:12
 */

namespace EB78\CustomI18nRouterBundle\tests\Twig;

use EB78\CustomI18nRouterBundle\Twig\CustomRoutingExtension;
use PHPUnit\Framework\TestCase;
use Mockery as m;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;

class CustomRoutingExtensionTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @var m\MockInterface */
    private $container;
    /** @var m\MockInterface */
    private $request;
    /** @var m\MockInterface */
    private $router;
    /** @var CustomRoutingExtension */
    private $extension;

    public function setUp()
    {
        $this->container = m::mock(ContainerInterface::class);
        $requestStack = m::mock(RequestStack::class);
        $this->request = m::mock(Request::class);
        $this->router = m::mock(Router::class);

        $requestStack
            ->shouldReceive('getMasterRequest')
            ->once()
            ->andReturn($this->request);

        $this->extension = new CustomRoutingExtension(
            $this->container,
            $requestStack,
            $this->router
        );
    }

    public function testGetPathShouldReturnAString()
    {
        $default_locale = 'fr-fr';
        $this
            ->container
            ->shouldReceive('hasParameter')
            ->once()
            ->with('default_locale')
            ->andReturn(true);

        $this
            ->container
            ->shouldReceive('getParameter')
            ->once()
            ->with('default_locale')
            ->andReturn($default_locale);

        $this
            ->router
            ->shouldReceive('generate')
            ->withAnyArgs()
            ->andReturn('/test');

        $result = $this->extension->getPath('home.index');
        $this->assertSame('/test', $result);
    }
}
