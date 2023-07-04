<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Venue;
use Faker\Factory;

class VenueFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 1; $i <= 20; $i++) {
            $venue = new Venue();
            $venue->setName($faker->realText(30));
            $venue->setStreet($faker->streetAddress(40));
            $venue->setLatitude($faker->latitude(min: 43, max: 51));
            $venue->setLongitude($faker->longitude(min: -4, max: 8));
            $venue->setCity($this->getReference('city' . mt_rand(1, sizeof(CityFixtures::CITIES))));
            $manager->persist($venue);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [CityFixtures::class];
    }
}
