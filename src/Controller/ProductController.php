<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CommentaryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products/{themeid}', name: 'app_product')]
    public function index($themeid, ThemeRepository $themeRepository, CommentaryRepository $commentaryRepository, ProductRepository $productRepository): Response
    {

        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();

        $ratings = $commentaryRepository->findAverageRatings();

        // Préparez un tableau pour stocker les produits avec leurs notes
        $productsWithRatings = [];

        // Pour chaque note, trouvez le produit correspondant et combinez les données
        foreach ($ratings as $rating) {
            // Recherchez chaque produit par son identifiant
            $product = $productRepository->find($rating['productId']);

            if ($product) {
                // Ajoutez le produit et sa note moyenne au tableau
                $productsWithRatings[] = [
                    'product' => $product,
                    'avg_rating' => $rating['avg_rating'],
                ];
            }
        }
        
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'categories' => $categories,
            'themeid' => $themeid,
            'entries' => $productsWithRatings
        ]);
    }

    #[Route('/product/{themeid}/{productid}', name: 'show_product')]
    public function show_product($themeid, $productid, ThemeRepository $themeRepository, ProductRepository $productRepository): Response
    {

        $product = $productRepository->findBy(['id' => $productid]);
        $theme = $themeRepository->findByID($themeid);

        return $this->render('product/show.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'product' => $product,
            'theme' => $theme
        ]);
    }
}
