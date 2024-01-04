<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/gerant/dashboard_admin', name: 'app_dashboard_admin')]
    public function index(OrderRepository $orderRepository): Response
    {
        $ongoingOrders = $orderRepository->getOngoingOrders();
        $closedOrders = $orderRepository->getClosedOrders();

        return $this->render('dashboard/admin.html.twig', [
            'ongoingOrders' => $ongoingOrders,
            'closedOrders' => $closedOrders,
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/gerant/list_products', name: 'list_products')]
    public function listProducts(PaginatorInterface $paginator, Request $request, ProductRepository $productRepository): Response
    {

        $products = $paginator->paginate(
            $productRepository->findAllQuery(),
            $request->query->getInt('page', 1),
            10 // 25 items per page
        );

        return $this->render('dashboard/listProducts.html.twig', [
            'controller_name' => 'DashboardController',
            'products' => $products
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/download-invoice/{id}', name: 'path_to_invoice')]
    public function downloadInvoice($id, OrderRepository $orderRepository): Response
    {
        // Récupérez l'ordre et le chemin de la facture
        $order = $orderRepository->find($id);
        $invoicePath = $this->getParameter('pdf_directory') . '/facture-' . $order->getId() . '.pdf';

        // Assurez-vous que la facture existe
        if (!file_exists($invoicePath)) {
            throw $this->createNotFoundException('La facture n\'existe pas');
        }

        // Retournez la facture comme réponse de fichier
        return $this->file($invoicePath, 'facture-' . $order->getId() . '.pdf');
    }
}
