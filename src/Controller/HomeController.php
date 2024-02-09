<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository, Request $request): Response
    {
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findTopRatedProductsByTheme();
        $screenWidth = $request->query->get('screenWidth');
        dump($screenWidth);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categories,
            'products' => $products,
            'screenWidth' => $screenWidth,
        ]);
    }

    #[Route('/mentions', name: 'mentions_legales')]
    public function mentions(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        
        return $this->render('mentions.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categories,
        ]);
    }

    #[Route('/cgv', name: 'cgv')]
    public function cgv(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        
        return $this->render('cgv.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categories,
        ]);
    }

    #[Route('/cgu', name: 'cgu')]
    public function cgu(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        
        return $this->render('cgu.html.twig', [
            'controller_name' => 'HomeController',
            'categories' => $categories,
        ]);
    }
}
