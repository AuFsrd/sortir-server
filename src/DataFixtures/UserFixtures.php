<?php

namespace App\DataFixtures;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class UserFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $sites = $manager->getRepository(Site::class)->findAll();

        $nullUser = new User();
        $nullUser->setUsername('Archived user');
        $nullUser->setEmail('null@sortir-eni.fr');
        $nullUser->setFirstName('NA');
        $nullUser->setLastName('NA');
        $nullUser->setPhone(str_replace(' ','',$faker->phoneNumber()));
        $pwd=$this->userPasswordHasher->hashPassword($nullUser,'123456');
        $nullUser->setPassword($pwd);
        $nullUser->setRoles(['ROLE_USER']);
        $nullUser->setSite($faker->randomElement($sites));
        $nullUser->setAdministrator(false);
        $manager->persist($nullUser);

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@sortir-eni.fr');
        $admin->setFirstName($faker->firstName);
        $admin->setLastName($faker->lastName);
        $admin->setPhone(str_replace(' ','',$faker->phoneNumber()));
        $pwd=$this->userPasswordHasher->hashPassword($admin,'123456');
        $admin->setPassword($pwd);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setSite($faker->randomElement($sites));
        $admin->setAdministrator(true);
        $manager->persist($admin);

        for($i=1;$i<=20;$i++){
            $user = new User();
            $tempName=$faker->userName . $i;
            $user->setUsername($tempName);
            $user->setEmail("$tempName@eni-ecole.fr");
            $pwd=$this->userPasswordHasher->hashPassword($user,'123456');
            $user->setPassword($pwd);

            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setPhone(str_replace(' ','',$faker->phoneNumber()));
            $user->setRoles(['ROLE_USER']);

            $user->setSite($faker->randomElement($sites));
            $manager->persist($user);
        }
        $manager->flush();
    }

    public function getDependencies():array
    {
        return [SiteFixtures::class];
    }
}
