<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ErrorController extends AbstractController
{
    public function show(FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        return $this->render('bundles/TwigBundle/Exception/error.html.twig', [
            "code" => $exception->getStatusCode(),
            "message" =>$exception->getMessage()
        ]);
    }
}
