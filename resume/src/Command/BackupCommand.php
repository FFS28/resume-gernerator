<?php

namespace App\Command;

use App\Service\CompanyService;
use App\Service\DeclarationService;
use App\Service\InvoiceService;
use App\Service\ReportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class BackupCommand extends Command
{
    protected static $defaultName = 'app:backup';

    protected $appKernel;
    protected $params;
    protected $mailer;

    public function __construct(
        string $name = null,
        KernelInterface $appKernel,
        ParameterBagInterface $params,
        MailerInterface $mailer,
    ) {
        parent::__construct($name);
        $this->mailer = $mailer;
        $this->params = $params;
        $this->appKernel = $appKernel;
    }

    protected function configure()
    {
        $this
            ->setDescription('Envoi des backups par email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $backupFile = false;
        $backupName = false;

        $finder = new Finder();
        $finder->files()
            ->name('*.dump')
            ->sortByName()
            ->in($this->appKernel->getProjectDir().$this->params->get('BACKUP_DIRECTORY'));

        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $backupName = $file->getFilename();
                $backupFile = $file->getPathname();
            }
        }

        if ($backupFile && $backupName) {
            $email = (new Email())
                ->from($this->params->get('MAILER_FROM'))
                ->to($this->params->get('MAILER_FROM'))
                ->subject($this->params->get('MAILER_SUBJECT') . ' Backup')
                ->attachFromPath(
                    $backupFile,
                    $backupName);;

            $this->mailer->send($email);

            $io->success('Backup envoyé');
        } else {
            $io->success('Aucun backup envoyé');
        }

        return 0;
    }
}
