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
    public function index($themeid, $id, PaginatorInterface $paginator, Request $request, ThemeRepository $themeRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);
        $theme = $themeRepository->findOneBy(['id' => $themeid]);
        $categories = $theme->getCategories();
        
        $query = $productRepository->findProductsByCategory($category->getId());

        $productsWithPictures = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            8
        );
        
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'productsWithPictures' => $productsWithPictures,
            'categoryName' => $category->getCategoryName(),
            'theme' => $theme,
            'themeid' => $themeid,
            'themes' => $themeRepository->findAll(),
            'categories' => $categories
        ]);
    }
}
