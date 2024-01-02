<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/gerant/dashboard_admin', name: 'app_dashboard_admin')]
    public function index(): Response
    {

        return $this->render('dashboard/admin.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/gerant/admin_list_product', name: 'admin_list_product')]
    public function admin_list_product(ProductRepository $productRepository): Response{
        $products = $productRepository->findAll();
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
}
