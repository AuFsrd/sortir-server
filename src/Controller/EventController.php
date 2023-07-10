<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Status;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/event')]
class EventController extends AbstractController
{

    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request                $request,
                        EntityManagerInterface $em
    ): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setStatus($em->getRepository(Status::class)->findOneBy(['name' => 'CREATED']));

            // règle gestion maxInscrits => modif status si besoin
            $participants = $form->get('participants')->getData();
            $flashCount = 0;
            if ($participants->count() > $event->getMaxParticipants()) {
//                dump("too many participants");
                $flashCount++;
                $this->addFlash('danger', "The maximum number of participants ("
                    . $event->getMaxParticipants() . ") has been exceeded"
                    . " (" . $participants->count() . ").");

            } elseif ($participants->count() == $event->getMaxParticipants()) {
                $event->setStatus($em->getRepository(Status::class)->findOneBy(['name' => 'CLOSED']));
            }

            // Registrationdeadline and starting date check
            if ($form->get('registrationDeadline')->getData() > $form->get('startDateTime')->getData()) {
                $flashCount++;
                $this->addFlash('danger', "Registration deadline ("
                    . $form->get('registrationDeadline')->getData()->format('Y-m-d H:m') . ") can't exceed starting date"
                    . " (" . $form->get('startDateTime')->getData()->format('Y-m-d H:m') . ").");
            }

            if ($flashCount == 0) {
                $eventRepository = $em->getRepository(Event::class);
                $eventRepository->save($event, true);
                return $this->redirectToRoute("app_event_show", ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
            } else {
                return $this->render('event/edit.html.twig', [
                    'event' => $event,
                    'form' => $form,
                ]);
            }
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);


    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {

        return $this->render('event/show.html.twig', [
            'event' => $event,

        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event,
                         EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // règle gestion maxInscrits => modif status si besoin
            $participants = $form->get('participants')->getData();
            $flashCount = 0;

            if ($participants->count() > $event->getMaxParticipants()) {
//                dump("too many participants");
                $flashCount++;
                $this->addFlash('danger', "The maximum number of participants ("
                    . $event->getMaxParticipants() . ") has been exceeded"
                    . " (" . $participants->count() . ").");

            } elseif ($participants->count() == $event->getMaxParticipants()) {
                $event->setStatus($em->getRepository(Status::class)->findOneBy(['name' => 'CLOSED']));
            }

            // Registrationdeadline and starting date check
            if ($form->get('registrationDeadline')->getData() > $form->get('startDateTime')->getData()) {
                $flashCount++;
                $this->addFlash('danger', "Registration deadline ("
                    . $form->get('registrationDeadline')->getData()->format('Y-m-d H:m') . ") can't exceed starting date"
                    . " (" . $form->get('startDateTime')->getData()->format('Y-m-d H:m') . ").");
            }

            if ($flashCount == 0) {
                $eventRepository = $em->getRepository(Event::class);
                $eventRepository->save($event, true);
                return $this->redirectToRoute("app_event_show", ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
            } else {
                return $this->render('event/edit.html.twig', [
                    'event' => $event,
                    'form' => $form,
                ]);
            }
        }
        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
//app_event_cancel
    #[Route('/{id}', name: 'app_event_cancel', methods: ['POST'])]
    public function cancel(Request $request, Event $event, EventRepository $eventRepository,
        StatusRepository $statusRepository,
        UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('cancel' . $event->getId(), $request->request->get('_token'))) {
            try {
                $event->setStatus($statusRepository->findOneBy(['name'=>'CANCELLED']));
                //Participants list retrieval
                $usersAsParticipants = $event->getParticipants();
                $event->removeParticipants($usersAsParticipants);
                $userRepository->saveList($usersAsParticipants, true);
                $eventRepository->save($event, true);

                $this->addFlash('success', 'Event successfully cencelled');
            } catch(\Exception $e) {
                $this->addFlash('alert', 'Event impossible to cancel.');
            }

        } else {
            $this->addFlash('alert', 'Event impossible to cancel.');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/{id}/delete', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token')))
        {

            $eventRepository->remove($event, true);
            $this->addFlash('success', 'Event successfully deleted.');
        } else {

            $this->addFlash('alert', 'Deletion failed.');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }


}
