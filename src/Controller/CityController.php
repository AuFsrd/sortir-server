<?php

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/city', name: 'city_')]
class CityController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(CityRepository $cityRepository): Response
    {
        $cities = $cityRepository->findAll();
        return $this->render('city/list.html.twig', [
            'cities' => $cities,
        ]);
    }
}
