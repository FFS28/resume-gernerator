<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create a user',
    aliases: []
)]
class UserCreateCommand extends Command
{
    public function __construct(
        protected UserPasswordHasherInterface $passwordHasher,
        protected EntityManagerInterface $entityManager,
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username')
            ->addArgument('role', InputArgument::OPTIONAL, 'Role')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $username = $input->getArgument('username');
        $role = $input->getArgument('role');

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username ?: $email);
        $user->setPlainPassword($password);
        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
        $user->setIsActive(true);
        $user->setRoles($role ? [$role] : ['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io = new SymfonyStyle($input, $output);
        $output->writeln('- email : ' . $user->getEmail());
        $io->writeln('- username : ' . $user->getUsername());
        $io->writeln('- password : ' . $user->getPlainPassword());
        $io->writeln('- role : ' . $user->getRoles()[0]);
        $io->success('User created');

        return 0;
    }
}
