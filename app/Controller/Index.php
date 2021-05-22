<?php

namespace App\Controller;

use App\Api\AbstractController;
use App\Attributes\UrlAttribute;
use Psr\Http\Message\ResponseInterface;

class Index extends AbstractController
{
    #[UrlAttribute('/helloworld[/{name}]')]
    public function helloworld(?string $name = 'mom'): ResponseInterface
    {
        return $this->render('helloworld.twig', ['name' => $name]);
    }
}