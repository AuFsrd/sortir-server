<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use App\Entity\Venue;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('startDateTime')
            ->add('duration')
            ->add('registrationDeadline')
            ->add('maxParticipants')
            ->add('description')
            ->add('status', EntityType::class, [
                'label' => 'Status',
                'class' => Status::class,
                'choice_label' => 'name',
                'placeholder' => '--Choose a status--'
            ])
            ->add('venue', EntityType::class, [
                'label' => 'Venue',
                'class' => Venue::class,
                'choice_label' => 'name',
                'placeholder' => '--Choose a venue--'
            ])
            ->add('organiser', EntityType::class, [
                'label' => 'Organiser',
                'class' => User::class,
                'choice_label' => 'fullname',
                'placeholder' => '--Choose a organiser--'
            ])
            ->add('participants', EntityType::class, [
                'label' => 'Participants',
                'class' => User::class,
                'choice_label' => 'fullname',
                'placeholder' => '--Choose one or more participant(s)--',
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.lastName', 'ASC')
                        ->addOrderBy('u.firstName', 'ASC');

                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
