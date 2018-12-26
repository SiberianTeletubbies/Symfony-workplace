<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetAdminCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this->setName('app:set-admin')
            ->addArgument('username', InputArgument::REQUIRED, 'Имя пользователя');;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $userName = $input->getArgument('username');
        /* @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $userName]);
        if ($user) {
            if (!$user->hasRole('ROLE_ADMIN')) {
                $user->addRole('ROLE_ADMIN');
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $output->writeln("Пользователю $userName назначена роль \"ROLE_ADMIN\"");
            } else {
                $output->writeln("Пользователю уже назначена роль \"ROLE_ADMIN\"");
            }
        } else {
            $output->writeln("Пользователь не найден");
        }
    }
}
