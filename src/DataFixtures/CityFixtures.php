<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\City;

class CityFixtures extends Fixture
{
    final const CITIES = [
        'Rennes'=>'35000',
        'Nantes'=>'44000',
        'Paris'=>'75000',
        'Bordeaux'=>'64000',
        'Saint-Malo'=>'35400',
        'Lille'=>'59000',
        'Marseille'=>'13000',
        'Angers'=>'49000',
        'Strasbourg'=>'67000'];
    public function load(ObjectManager $manager): void
    {
        $i = 0;
        foreach (CityFixtures::CITIES as $n=>$p) {
            $i++;
            $city = new City();
            $city->setName($n);
            $city->setPostCode($p);
            $this->addReference("city{$i}", $city);
            $manager->persist($city);
        }

        $manager->flush();
    }
}
