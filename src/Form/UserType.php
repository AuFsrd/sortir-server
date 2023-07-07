<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\StringType;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
//            ->add('roles', CollectionType::class, [
//                'entry_type' => TextType::class,
//                'entry_options' => [
//                    'attr' => ['class' => 'roles'],
//                ]
//            ])

            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => true,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('firstName')
            ->add('lastName')
            ->add('phone')
            ->add('email')
            ->add('administrator', CheckboxType::class, [
                'label' => 'Is admin',
                'required' => false,

            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Is active',
                'required' => false,
            ])
            ->add('site', EntityType::class, [
                'label' => 'Site',
                'class' => Site::class,
                'choice_label' => 'name',
                'placeholder' => '--Choose a site--'
            ])
            ->add('image', FileType::class, [
                'required'=>false,
                'mapped'=>false,
                'constraints'=> [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage'=>'Please upload a valid image'
                    ])
                ]
            ])
//            ->add('eventsAsParticipant')
//            ->add('eventsAsParticipant')
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event){
            $user=$event->getData();
            if($user && $user->getFileName()) {
                $form=$event->getForm();

                $form->add('deleteImage', CheckboxType::class, [
                    'required'=>false,
                    'mapped'=>false
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
