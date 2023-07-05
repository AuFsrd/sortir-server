<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/site', name: 'site_')]
class SiteController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(SiteRepository $siteRepository): Response

    {
        $sites = $siteRepository->findAll();
        return $this->render('site/list.html.twig', [
            'sites' => $sites,
        ]);
    }
}
