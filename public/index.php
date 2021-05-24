<?php

# Initialize the application
/** @var \App\Bootstrap $bootstrap */
[$bootstrap, $autoloader] = require(dirname(__DIR__, 1) . '/app/init.php');

# Load the dotenv
(\Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1)))->load();

# Get the container
$container = $bootstrap->getContainer();

# Init the PSR17 Factory
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

# Init slim
$app = \Slim\Factory\AppFactory::create($psr17Factory, $container);

# Load all the routes
foreach ($autoloader->getClassMap() as $class => $path) {
    if (str_starts_with($class, 'App\\Controller')) {
        try {
            /** @var \App\Api\AbstractController $loaded */
            $reflectionClass = new ReflectionClass($class);
            foreach ($reflectionClass->getMethods() as $method) {
                $attributes = $method->getAttributes(\App\Attributes\UrlAttribute::class);
                foreach ($attributes as $attribute) {
                    $apiUrl = $attribute->newInstance();
                    $loaded = $container->get($class);
                    $app->map($apiUrl->getType(), $apiUrl->getRoute(), $loaded($method->getName()));
                }
            }
        } catch (\Throwable $e) {
            throw new RuntimeException('Error loading controller: ' . $e->getMessage());
        }
    }
}

# Add middleware
foreach ($autoloader->getClassMap() as $class => $path) {
    if (str_starts_with($class, 'App\\Middleware')) {
        /** @var \Psr\Http\Server\MiddlewareInterface $loaded */
        $loaded = $container->get($class);
        $app->addMiddleware($loaded);
    }
}

# Run the app
$app->run();