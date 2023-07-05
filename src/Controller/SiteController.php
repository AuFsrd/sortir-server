<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/site', name: 'site_')]
class SiteController extends AbstractController
{
    #[Route('', name: 'list')]
    public function list(): Response
    {
        return $this->render('site/list.html.twig', [
            'controller_name' => 'SiteController',
        ]);
    }
}
