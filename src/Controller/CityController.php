<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/city')]
class CityController extends AbstractController
{
    #[Route('/', name: 'app_city_index', methods: ['GET'])]
    public function index(CityRepository $cityRepository): Response
    {
        return $this->render('city/index.html.twig', [
            'cities' => $cityRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_city_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CityRepository $cityRepository, SerializerInterface $serializer): Response
    {
        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);
//        if ($form->isSubmitted() && isset($_POST['check_button'])) {
//            $data=$form->getData();
//            $name = $data->getName()?$data->getName().'&type=municipality':'';
//            $postcode = $data->getPostcode()??'';
//            $param = ($name && $postcode)? $name.'+'.$postcode:$name.$postcode;
//            $cities = file_get_contents('https://api-adresse.data.gouv.fr/search/?q='.$param.'&autocomplete=1');
//            $citiesTab = $serializer->decode($cities,'json');
//            dd($citiesTab['features']);
//
//        } elseif ($form->isSubmitted() && $form->isValid() && isset($_POST['save_button'])) {
        if  ($form->isSubmitted() && $form->isValid()) {

            $cityRepository->save($city, true);

            return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('city/new.html.twig', [
            'city' => $city,
            'form' => $form,
        ]);
    }

/*    #[Route('/new', name: 'app_city_api', methods:['GET','POST'])]
    public function events(Request $request, SerializerInterface $serializer): Response
    {
        $param="";
        if ($eventsForm->isSubmitted()) {
            $data=$eventsForm->getData();
            $date=$data['start_date']->format('Y-m-d');
            $param = "&refine.location_city=$data[city]"."&refine.firstdate_begin=$date";
        }
        $events = file_get_contents('https://public.opendatasoft.com/api/records/1.0/search/?dataset=evenements-publics-openagenda'.$param);
        $eventsTab = $serializer->decode($events,'json');

//        dd($eventsTab);
        return $this->renderForm('api/events.html.twig', [
            'events' => $eventsTab,
            'eventsForm'=>$eventsForm
        ]);
    }*/

    #[Route('/{id}', name: 'app_city_show', methods: ['GET'])]
    public function show(City $city): Response
    {
        return $this->render('city/show.html.twig', [
            'city' => $city,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_city_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, City $city, CityRepository $cityRepository): Response
    {
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $cityRepository->save($city, true);

            return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('city/edit.html.twig', [
            'city' => $city,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_city_delete', methods: ['POST'])]
    public function delete(Request $request, City $city, CityRepository $cityRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$city->getId(), $request->request->get('_token'))) {
            $cityRepository->remove($city, true);
        }

        return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
    }
}
