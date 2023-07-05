<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(): Response
    {
        return $this->render('user/list.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
