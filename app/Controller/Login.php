<?php

namespace App\Controller;

use App\Api\AbstractController;
use App\Attributes\UrlAttribute;
use Psr\Http\Message\ResponseInterface;

class Login extends AbstractController
{
    #[UrlAttribute('/login', ['GET'])]
    public function loginGet(): ResponseInterface
    {
        return $this->render('login.twig');
    }

    #[UrlAttribute('/login', ['POST'])]
    public function loginPost(): ResponseInterface
    {
        $username = $this->getPostParam('username');
        $password = $this->getPostParam('password');

        return $this->render('loginPost.twig', ['username' => $username, 'password' => $password]);
    }
}