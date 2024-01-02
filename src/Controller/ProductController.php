<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\FileUploader;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Repository\CommentaryRepository;
use App\Repository\ProductRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            'theme' => $theme,
            'categories' => $categories,
            'entries' => $productsWithRatings,
        ]);
    }

    //Get specific product in specific category/theme
    #[Route('/product/{themeid}/{categoryid}/{productid}', name: 'show_product_with_category')]
    public function show_product_with_category($themeid, $categoryid, $productid, CategoryRepository $categoryRepository, ThemeRepository $themeRepository, ProductRepository $productRepository): Response
    {
        $category = $categoryRepository->findOneBy(['id' => $categoryid]);
        $product = $productRepository->findOneBy(['id' => $productid]);
        $theme = $themeRepository->findByID($themeid);
        $categories = $theme->getCategories();
        $pictures = $product->getPictures();
        $ingredients = $product->getIngredients();


        return $this->render('product/show_product_with_category.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'product' => $product,
            'pictures' => $pictures,
            'ingredients' => $ingredients,
            'theme' => $theme,
            'category' => $category,
            'categories' => $categories
        ]);
    }

    #[Route('/product/disable/{productid}', name: 'disable_product')]
    public function disable_product($productid, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {

        $product = $productRepository->findOneBy(['id' => $productid]);
        $products = $productRepository->findAll();

        $product->setIsActive(false);

        $em->persist($product);
        $em->flush();


        return $this->render('dashboard/listProducts.html.twig', [
            'controller_name' => 'DashboardController',
            'products' => $products,
        ]);
    }

    #[Route('/product/enable/{productid}', name: 'enable_product')]
    public function enable_product($productid, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {

        $product = $productRepository->findOneBy(['id' => $productid]);
        $products = $productRepository->findAll();

        $product->setIsActive(true);

        $em->persist($product);
        $em->flush();


        return $this->render('dashboard/listProducts.html.twig', [
            'controller_name' => 'DashboardController',
            'products' => $products,
        ]);
    }

    #[Route('/product/delete/{productid}', name: 'delete_product')]
    public function delete_product($productid, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {

        $product = $productRepository->findOneBy(['id' => $productid]);

        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('list_products');
    }

    #[Route('/product/update/{productid}', name: 'update_product')]
    public function update_product($productid, Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $product = $productRepository->findOneBy(['id' => $productid]);

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        // dd($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // /** @var FileUploader $profilePictureFile */
            $profilePictureFile = $form->get('profilePicture')->getData();

            dd($profilePictureFile);

            $product = $form->getData();
            
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('listProducts');
        }

        return $this->render('product/updateProduct.html.twig', [
            'updateProductForm' => $form->createView(),
            'controller_name' => 'DashboardController'
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
        $picture = $pictures[0];
        
        return $this->render('product/show_product.html.twig', [
            'controller_name' => 'ProductController',
            'themes' => $themeRepository->findAll(),
            'product' => $product,
            'theme' => $theme,
            'pictures' => $pictures,
            'picture' => $picture,
            'ingredients' => $ingredients,
            'categories' => $categories
        ]);
    }
}
