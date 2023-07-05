<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /*
    public function login(): JsonResponse
    {
        $user = $this->getUser();
        return $this->json(
            [
                'username' => $user->getUserIdentifier(),
                'roles' => $user->getRoles()
            ]
        );
    }
    */
}