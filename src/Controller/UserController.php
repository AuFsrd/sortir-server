<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', name: 'user_')]
class UserController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route(path:"/{id}",name:"detail", requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if(!$user){
            throw $this->createNotFoundException('Unknown user');
        }
        return $this->render('user/detail.html.twig', [
            'user' => $user
        ]);
    }
}
