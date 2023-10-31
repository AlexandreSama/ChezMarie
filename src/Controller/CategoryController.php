<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/{themeid}/{id}', name: 'app_category')]
    public function index($themeid, $id, ThemeRepository $themeRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        $theme = $themeRepository->findOneBy(['id' => $themeid]);
        $categories = $theme->getCategories();
        $products = $category->getProducts();

        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'products' => $products,
            'theme' => $theme,
            'themeid' => $themeid,
            'themes' => $themeRepository->findAll(),
            'categories' => $categories
        ]);
    }
}
