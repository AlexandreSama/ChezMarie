<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products/{themeid}', name: 'app_product')]
    public function index($themeid, ThemeRepository $themeRepository): Response
    {

        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();
        $allProducts = [];

        foreach ($categories as $category) {
            foreach ($category->getProducts() as $product) {
                array_push($allProducts, $product);
            }
        }

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'categories' => $categories,
            'products' => $allProducts,
            'themeid' => $themeid
        ]);
    }

    #[Route('/product/{themeid}/{id}', name: 'show_product')]
    public function show_product(Product $product, $themeid, ThemeRepository $themeRepository): Response{

        $theme = $themeRepository->findByID($themeid);

        return $this->render('product/show.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'product' => $product
        ]);
    }
}
