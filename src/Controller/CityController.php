<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/city', name: 'city_')]
class CityController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(): Response
    {
        return $this->render('city/list.html.twig', [
            'controller_name' => 'CityController',
        ]);
    }
}
