<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Route('/fetchtest', name: 'app_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['item' => 'Hello world!']);
    }
}