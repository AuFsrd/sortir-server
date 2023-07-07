<?php

namespace App\Controller;

use App\Entity\Status;
use App\Form\StatusType;
use App\Repository\StatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/status')]
class StatusController extends AbstractController
{
    #[Route('/', name: 'app_status_index', methods: ['GET'])]
    public function index(StatusRepository $stateRepository): Response
    {
        return $this->render('status/index.html.twig', [
            'statuses' => $stateRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_state_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StatusRepository $stateRepository): Response
    {
        $state = new Status();
        $form = $this->createForm(StatusType::class, $state);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stateRepository->save($state, true);

            return $this->redirectToRoute('app_state_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('status/new.html.twig', [
            'status' => $state,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_state_show', methods: ['GET'])]
    public function show(Status $state): Response
    {
        return $this->render('status/show.html.twig', [
            'status' => $state,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_state_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Status $state, StatusRepository $stateRepository): Response
    {
        $form = $this->createForm(StatusType::class, $state);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stateRepository->save($state, true);

            return $this->redirectToRoute('app_state_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('status/edit.html.twig', [
            'status' => $state,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_state_delete', methods: ['POST'])]
    public function delete(Request $request, Status $state, StatusRepository $stateRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$state->getId(), $request->request->get('_token'))) {
            $stateRepository->remove($state, true);
        }

        return $this->redirectToRoute('app_state_index', [], Response::HTTP_SEE_OTHER);
    }
}
