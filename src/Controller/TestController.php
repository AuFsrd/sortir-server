<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/fetchtest', name: 'test_', methods: ['GET'])]
class TestController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['item' => 'Hello world!']);
    }
}