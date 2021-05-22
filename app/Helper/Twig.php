<?php

namespace App\Helper;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class Twig
{
    protected Environment $twig;

    public function __construct(
    ) {
        $loader = new FilesystemLoader(dirname(__DIR__, 2) . '/templates');
        $this->twig = new Environment($loader, [
            'cache' => dirname(__DIR__, 2) . '/cache',
            'debug' => true,
            'auto_reload' => true,
            'strict_variables' => true,
            'optimizations' => true
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(string $templatePath, array $data = []): string
    {
        if (pathinfo($templatePath, PATHINFO_EXTENSION) !== 'twig') {
            throw new \RuntimeException('Error, twig templates need to end in .twig');
        }

        return $this->twig->render($templatePath, $data);
    }
}