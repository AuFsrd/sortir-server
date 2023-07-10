<?php

// /src/AppBundle/Command/CsvImportCommand.php

namespace App\Service;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CsvImport
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * CsvImportCommand constructor.
     *
     * @param EntityManagerInterface $em
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(EntityManagerInterface $em, private readonly string $csvTargetDirectory)
    {
        $this->em = $em;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception
     */
    public function importCsv(UserPasswordHasher $userPasswordHasher, UploadedFile $file
    ): int
    {

        $csv = Reader::createFromPath($this->csvTargetDirectory.'/'.$file->getClientOriginalName(),'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $results = $csv->getRecords();
        $nb= iterator_count($results);
        $nbFails = 0;
        // create new user
        foreach ($results as $row) {
            $user = new User();
            $user->setUsername($row['username'])
                ->setRoles([$row['role']])
                ->setPassword($userPasswordHasher->hashPassword(
                    $user,
                    $row['password']))
                ->setFirstName($row['firstName'])
                ->setLastName($row['lastName'])
                ->setPhone($row['phone'])
                ->setEmail($row['email'])
                ->setAdministrator($row['administrator'])
                ->setActive($row['active'])
                ->setSite($this->em->getRepository(Site::class)->find($row['site']))
            ;
            /*dump($user);*/
            if ($this->em->getRepository(User::class)->findOneBy([
                    'email'=>$row['email']
                ]) || $this->em->getRepository(User::class)->findOneBy([
                    'username'=>$row['username']
                ])) {
                /*$this->addFlash('danger', "$user->getUsername() already exists.");*/
                $nbFails++;
            } else {
                /*$this->addFlash('success', "$user->getUsername() successfully added.");*/
                $this->em->persist($user);
            }
        }

        // save / write the changes to the database
        $this->em->flush();
/*        $io->progressFinish();
        $io->success($nbFails. " imports failed.");
        $io->success('Command exited cleanly!');*/
        return $nb-$nbFails;
    }
}