<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends EasyAdminController
{
    /**
     * @Route("/admin/dashboard/{year<\d+>?0}/{quarter<\d+>?0}", name="dashboard")
     */
    public function index(InvoiceRepository $invoiceRepository, int $year = 0, int $quarter = 0)
    {
        $data = [];
        $data['year'] = $year ? $year : (new \DateTime())->format('Y');
        $data['quarter'] = $quarter ? $quarter : ceil((new \DateTime())->format('n') / 3);
        $data['years'] = $invoiceRepository->findYears();
        $data['revenuesByYears'] = $invoiceRepository->getSalesRevenuesGroupBy('year');
        $data['revenuesByMonth'] = $invoiceRepository->getSalesRevenuesGroupBy('month', $data['year']);
        $data['revenuesByQuarter'] = $invoiceRepository->getSalesRevenuesGroupBy('quarter', $data['year']);
        $data['currentRevenues'] = $invoiceRepository->getSalesRevenuesBy($data['year']);
        $data['daysByMonth'] = $invoiceRepository->getDaysCountByMonth($data['year']);
        $data['unpayedInvoices'] = $invoiceRepository->findInvoicesBy(0, 0, false);
        $data['isOutOfLimit'] = $invoiceRepository->isOutOfLimit();
        $data['isOutOfTvaLimit'] = $invoiceRepository->isOutOfTvaLimit();

        //dump($data);

        return $this->render('page/dashboard.html.twig', $data);
    }
}
