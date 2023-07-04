<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Site;

class SiteFixtures extends Fixture
{
    final const SITES = [
        'Saint-Herblain',
        'Chartres-de-Bretagne',
        'Quimper',
        'La Roche-sur-Yon',
        'Niort'];
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        foreach (SiteFixtures::SITES as $s) {
            $site = new Site();
            $site->setName($s);
            $manager->persist($site);
        }

        $manager->flush();
    }
}
