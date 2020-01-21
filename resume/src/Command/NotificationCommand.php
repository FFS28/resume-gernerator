<?php

namespace App\Command;

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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class NotificationCommand extends Command
{
    protected static $defaultName = 'app:notifications';

    protected $params;
    protected $mailer;
    protected $templating;
    protected $translator;
    protected $declarationService;
    protected $invoiceService;
    protected $reportService;

    public function __construct(
        string $name = null,
        ParameterBagInterface $params,
        MailerInterface $mailer,
        Environment $templating,
        TranslatorInterface $translator,
        DeclarationService $declarationService,
        InvoiceService $invoiceService,
        ReportService $reportService
    ) {
        parent::__construct($name);
        $this->params = $params;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->templating = $templating;
        $this->declarationService = $declarationService;
        $this->invoiceService = $invoiceService;
        $this->reportService = $reportService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $notifications = array_merge(
            $this->invoiceService->getNotifications(),
            $this->declarationService->getNotifications(),
            $this->reportService->getNotifications()
        );

        if (count($notifications) > 0) {
            $email = (new Email())
                ->from($this->params->get('MAILER_FROM'))
                ->to($this->params->get('MAILER_FROM'))
                ->subject($this->params->get('MAILER_SUBJECT') . ' ' .
                    $this->translator->trans('Notifications'))
                ->text($this->templating->render(
                    'email/notifications.txt.twig',
                    ['notifications' => $notifications]
                ));

            $this->mailer->send($email);

            $io->success('Nouvelles notifications');
            $io->listing($notifications);
        } else {
            $io->success('Aucune nouvelle notification');
        }

        return 0;
    }
}
