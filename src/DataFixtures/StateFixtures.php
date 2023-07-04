<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\State;

class StateFixtures extends Fixture
{
    final const STATES = [
        'CREATED',
        'OPEN',
        'CLOSED',
        'IN PROGRESS',
        'PAST',
        'CANCELLED'];
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        foreach (StateFixtures::STATES as $s) {
            $state = new State();
            $state->setName($s);
            $manager->persist($state);
        }

        $manager->flush();
    }
}
