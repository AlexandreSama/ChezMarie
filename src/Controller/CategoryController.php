<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/{themeid}/{id}', name: 'app_category')]
<<<<<<< HEAD
    public function index($themeid, $id, PaginatorInterface $paginator, Request $request, ThemeRepository $themeRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
=======
    public function index($themeid, $id, ThemeRepository $themeRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
>>>>>>> 0d379d5785358cfcd0e5aa84098231bed34f8040
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        $theme = $themeRepository->findOneBy(['id' => $themeid]);
        $categories = $theme->getCategories();
<<<<<<< HEAD
        
        $query = $productRepository->findProductsByCategory($category->getId());
=======

        $products = $productRepository->findProductsByCategory($category->getId());
>>>>>>> 0d379d5785358cfcd0e5aa84098231bed34f8040

        $productsWithPictures = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8
        );
        
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
<<<<<<< HEAD
            'productsWithPictures' => $productsWithPictures,
            'categoryName' => $category->getCategoryName(),
=======
            'products' => $products,
            'category' => $category,
>>>>>>> 0d379d5785358cfcd0e5aa84098231bed34f8040
            'theme' => $theme,
            'themeid' => $themeid,
            'themes' => $themeRepository->findAll(),
            'categories' => $categories
        ]);
    }
}
