<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\State;

class StateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        foreach (State::STATES as $s) {
            $state = new State();
            $state->setName($s);
            $manager->persist($state);
        }

        $manager->flush();
    }
}
