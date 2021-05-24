<?php

namespace App\Controller;

use App\Api\AbstractController;
use App\Attributes\UrlAttribute;
use Psr\Http\Message\ResponseInterface;

class Index extends AbstractController
{
    #[UrlAttribute('/')]
    public function helloworld(): ResponseInterface
    {
        return $this->render('index.twig');
    }
}