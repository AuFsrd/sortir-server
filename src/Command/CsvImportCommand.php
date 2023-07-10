<?php

// /src/AppBundle/Command/CsvImportCommand.php

namespace App\Command;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class CsvImportCommand
 * @package AppBundle\ConsoleCommand
 */
class CsvImportCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CsvImportCommand constructor.
     *
     * @param EntityManagerInterface $em
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    /**
     * Configure
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('csv:import')
            ->setDescription('Imports the CSV data file')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output
                               ): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Attempting import for feed...');
        $csv = Reader::createFromPath('%kernel.project_dir%/../public/uploads/csv/users_import.csv','r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $results = $csv->getRecords();
        $nb= iterator_count($results);
        $io->progressStart($nb);
        $nbFails = 0;
        // create new user
        foreach ($results as $row) {
            $user = (new User())
                ->setUsername($row['username'])
                ->setRoles([$row['role']])
                ->setPassword(
                    /*$userPasswordHasher->hashPassword($user,*/
                        $row['password']
                    /*)*/
                )
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
                    $nbFails++;
            } else {
                $this->em->persist($user);
            }

            $io->progressAdvance();
        }




        // save / write the changes to the database
        $this->em->flush();
        $io->progressFinish();
        $io->success($nbFails. " imports failed.");
        $io->success('Command exited cleanly!');
        return $nb;
    }
}