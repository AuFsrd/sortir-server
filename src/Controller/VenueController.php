<?php

namespace App\Controller;

use App\Repository\VenueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/venue', name: 'venue_')]
class VenueController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(VenueRepository $venueRepository): Response
    {
        $venues = $venueRepository->findAll();
        return $this->render('venue/list.html.twig', [
            'venues' => $venues,
        ]);
    }
}
