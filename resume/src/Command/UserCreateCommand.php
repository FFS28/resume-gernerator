<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'app:user:create';

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $passwordEncoder;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a user')
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
        $user->setUsername($username ? $username : $email);
        $user->setPlainPassword($password);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPlainPassword()));
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
