<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Site;
use App\Entity\Status;
use App\Entity\User;
use App\Entity\Venue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class EventFixtures extends Fixture implements DependentFixtureInterface
{


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $statuses = $manager->getRepository(Status::class)->findAll();
        $venues = $manager->getRepository(Venue::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();


        for($i=1;$i<=50;$i++){
            $event = new Event();
            $event->setName($faker->realText(40));
            $tempDate = $faker->dateTimeBetween('-2 months', '+2 months');
            $tempDate = \DateTimeImmutable::createFromMutable($tempDate);
            $event->setStartDateTime($tempDate);
            $event->setDuration(mt_rand(30,60*24*30)); //1 mois max (en minutes)
            $event->setRegistrationDeadline($tempDate->sub(new \DateInterval('P1D')));
            $event->setMaxParticipants(mt_rand(2,10));
            $event->setDescription($faker->realText(150));
            $event->setStatus($faker->randomElement($statuses));
            $event->setVenue($faker->randomElement($venues));
//            $organiser = $faker->randomElement($users);
            shuffle($users);
//            $event->setOrganiser(array_values($users)[0]);
            $event->setOrganiser($users[0]);

            $tempUsers = $faker->randomElements(array_slice($users,1),mt_rand(0,$event->getMaxParticipants()),false);
            $event->addParticipants($tempUsers);



            $manager->persist($event);
        }
        $manager->flush();
    }

    public function getDependencies():array
    {
        return [UserFixtures::class];
    }
}
