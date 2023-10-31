<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\CommentaryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    //Get 6 best product from this theme
    #[Route('/products/{themeid}', name: 'app_product')]
    public function index($themeid, ThemeRepository $themeRepository, CommentaryRepository $commentaryRepository, ProductRepository $productRepository): Response
    {

        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();

        $ratings = $commentaryRepository->findAverageRatingsByTheme($themeid);

        // Préparez un tableau pour stocker les produits avec leurs notes et leurs images
        $productsWithRatings = [];

        // Pour chaque note, trouvez le produit correspondant et combinez les données
        foreach ($ratings as $rating) {
            // Recherchez chaque produit par son identifiant
            $product = $productRepository->find($rating['productId']);

            if ($product) {
                // Ajoutez le produit, sa note moyenne, et l'URL de l'image au tableau
                $productsWithRatings[] = [
                    'product' => $product,
                    'avg_rating' => $rating['avg_rating'],
                    'picture_url' => $rating['picture_url'], // ici, nous ajoutons l'URL de l'image
                    'picture_slug' => $rating['picture_slug']
                ];
            }
        }

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'categories' => $categories,
            'themeid' => $themeid,
            'entries' => $productsWithRatings,
        ]);
    }

    //Get specific product in specific category/theme
    #[Route('/product/{themeid}/{categoryid}/{productid}', name: 'show_product_with_category')]
    public function show_product_with_category($themeid, $categoryid, $productid, CategoryRepository $categoryRepository, ThemeRepository $themeRepository, ProductRepository $productRepository): Response
    {
        $category = $categoryRepository->findBy(['id' => $categoryid]);
        $product = $productRepository->findBy(['id' => $productid]);
        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();

        return $this->render('product/show_product_with_category.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'product' => $product,
            'theme' => $theme,
            'themeid' => $theme->getId(),
            'category' => $category,
            'categories' => $categories
        ]);
    }

    //Get one of the six best product in the theme
    #[Route('/product/{themeid}/{productid}', name: 'show_product_without_category')]
    public function show_product_without_category($themeid, $productid, ThemeRepository $themeRepository, ProductRepository $productRepository): Response
    {

        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();

        $product = $productRepository->findOneBy(['id' => $productid]);
        $theme = $themeRepository->findByID($themeid);

        $ingredients = $product->getIngredients();

        $pictures = $product->getPictures();


        return $this->render('product/show_product_without_category.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'product' => $product,
            'theme' => $theme,
            'themeid' => $theme->getId(),
            'pictures' => $pictures,
            'ingredients' => $ingredients,
            'categories' => $categories
        ]);
    }
}
