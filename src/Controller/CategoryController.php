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
    /**
     * This PHP function retrieves products belonging to a specific category and paginates them for
     * display in a category page.
     * 
     * @param themeid The `themeid` parameter represents the ID of a theme. It is used to retrieve a
     * specific theme from the database.
     * @param id The `id` parameter represents the ID of a specific category. It is used to retrieve
     * the category object from the database.
     * @param PaginatorInterface paginator The `` parameter is an instance of the
     * `PaginatorInterface` class, which is used for paginating query results. It allows you to split
     * large result sets into smaller pages.
     * @param Request request The `` parameter is an instance of the `Request` class, which
     * represents an HTTP request. It contains information about the request, such as the request
     * method, headers, query parameters, and request body.
     * @param ThemeRepository themeRepository The `` parameter is an instance of the
     * `ThemeRepository` class, which is responsible for retrieving and manipulating data related to
     * themes in the application. It is used in this method to fetch a specific theme based on the
     * `` parameter.
     * @param ProductRepository productRepository An instance of the ProductRepository class, which is
     * responsible for retrieving and manipulating product data from the database.
     * @param CategoryRepository categoryRepository An instance of the CategoryRepository class, which
     * is responsible for retrieving and manipulating category data from the database.
     * 
     * @return Response a Response object.
     */
    #[Route('/category/{id}', name: 'app_category')]
    public function index($id, PaginatorInterface $paginator, Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['id' => $id]);

        $categories = $categoryRepository->findAll();
        
        $query = $productRepository->findProductsByCategory($category->getId());

        $productsWithPictures = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'productsWithPictures' => $productsWithPictures,
            'categoryName' => $category->getCategoryName(),
            'categories' => $categories
        ]);
    }
}
