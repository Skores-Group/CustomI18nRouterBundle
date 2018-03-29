<?php
namespace EB78\CustomI18nRouterBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RouterCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container) {

        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('i18n:update')
            ->setDescription('update i18n files')
            ->setHelp('This command update all existent configuration files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*
        $routeCollection = $this->container->get('router')->getRouteCollection();
        $keys = $routes = [];
        foreach ($routeCollection as $key => $route) {
            if( 0 !== \strpos($key, '_')) {
                $routes[$key] = $route;
            }
        }
        foreach (array_keys($this->container->getParameterBag()->all()) as $route) {
            if( 0 === \strpos($route, 'i18n_')) {
                $keys[] = $route;
            }
        }
        */
    }
}
