<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/{themeid}/{id}', name: 'app_category')]
    public function index($themeid, $id, ThemeRepository $themeRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        $theme = $themeRepository->findOneBy(['id' => $themeid]);
        $categories = $theme->getCategories();

        $products = $productRepository->findProductsByCategory($category->getId());

        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'products' => $products,
            'category' => $category,
            'theme' => $theme,
            'themeid' => $themeid,
            'themes' => $themeRepository->findAll(),
            'categories' => $categories
        ]);
    }
}
