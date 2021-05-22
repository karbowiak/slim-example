<?php

namespace App;

use Composer\Autoload\ClassLoader;
use League\Container\Container;
use League\Container\ReflectionContainer;
use Psr\Container\ContainerInterface;

class Bootstrap
{
    public function __construct(
        protected ClassLoader $autoloader,
        protected ?ContainerInterface $container = null
    ) {
        $this->buildContainer();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function buildContainer(): void
    {
        // Instantiate the container unless we provide it one
        $this->container = $this->container ?? new Container();

        // Setup autowiring
        $this->container->delegate(
            new ReflectionContainer()
        );

        // Add the autoloader
        $this->container->add(ClassLoader::class, $this->autoloader);

        // Add the container to itself (keep this at the bottom)
        $this->container->add(Container::class, $this->container);
    }
}