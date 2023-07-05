<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/venue', name: 'venue_')]
class VenueController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(): Response
    {
        return $this->render('venue/list.html.twig', [
            'controller_name' => 'VenueController',
        ]);
    }
}
