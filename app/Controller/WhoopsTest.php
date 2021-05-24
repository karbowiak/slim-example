<?php

namespace App\Controller;

use App\Api\AbstractController;
use App\Attributes\UrlAttribute;
use Psr\Http\Message\ResponseInterface;

class WhoopsTest extends AbstractController
{
    #[UrlAttribute('/whoops')]
    public function whoops(): ResponseInterface
    {
        throw new \Exception('whoops, i made a boo boo');
    }
}