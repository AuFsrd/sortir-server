<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\csvImportType;
use App\Form\UserType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use App\Service\CsvImport;
use Doctrine\DBAL\Types\BooleanType;
use League\Csv\Exception;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
/*use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;*/

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository,
                        UserPasswordHasherInterface $userPasswordHasher,
                        FileUploader $fileUploader): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $imageFile = $form->get('image')->getData();
            if($imageFile){
                $user->setFilename($fileUploader->upload($imageFile, 'img'));
            }
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/csv', name: 'app_user_csv', methods: ['GET', 'POST'])]
    public function csv(Request $request, FileUploader $fileUploader, CsvImport $csv, UserPasswordHasherInterface $userPasswordHasher,): Response
    {
        $dataSet[]=[];
/*        $form = $this->createForm(csvImportType::class, $dataSet);
        $form->handleRequest($request);*/

        $csvForm=$this->createFormBuilder()
            ->add('file', FileType::class, [
                'required'=>false,
                'mapped'=>false,
                'constraints'=> [
                    new File([
                        'mimeTypes' => [
                            'text/csv',
                            'text/x-csv',
                            'text/plain',
                        ],
                        'mimeTypesMessage'=>'Please upload a valid csv file'
                    ])
                ]
            ])
            ->add('loading', HiddenType::class, [
                'data'=>false,
            ])
            ->getForm();
        $csvForm->handleRequest($request);
        if ($csvForm->isSubmitted()) {
            $file = $csvForm->get('file')->getData();
            if($file){
                $fileUploader->upload($file,'csv');
                //execute csv import script
                $nbUserUploaded = $csv->importCsv($userPasswordHasher, $file);
                /*$user->setFilename($fileUploader->upload($imageFile));*/
/*                $csvForm->setData(['loading'=>true]);*/
                $this->addFlash('success', "$nbUserUploaded new user(s) added");
            } else {
                $this->addFlash('danger', "Somehow I can't manage to upload this file :/");
            }

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/batch.html.twig', [
            'dataSet' => $dataSet,
            'form' => $csvForm,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository,
                         UserPasswordHasherInterface $userPasswordHasher,
                         FileUploader $fileUploader): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $imageFile = $form->get('image')->getData();
            if(($form->has('deleteImage') && $form['deleteImage']->getData())
            || $imageFile) {
                $fileUploader->delete($user->getFilename(), $this->getParameter('app.images_user_directory'));
                if($imageFile){
                    $user->setFilename($fileUploader->upload($imageFile, 'img'));
                } else {
                    $user->setFilename(null);
                }
            }
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user,
                           UserRepository $userRepository,
                           EventRepository $eventRepository): Response
    {

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $nullUser = $userRepository->findOneBy(['username'=>'Archived user']);
            foreach ($user->getEventsAsOrganiser() as $e) {
                $e->setOrganiser($nullUser);
            }

            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
